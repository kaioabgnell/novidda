<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Changelog;
use App\Models\ContextualBanner;
use App\Support\HtmlSanitizer;
use App\Support\WidgetCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChangelogController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'type'   => $request->query('type', ''),
            'status' => $request->query('status', ''),
            'from'   => $request->query('from', ''),
            'to'     => $request->query('to', ''),
        ];

        $changelogs = Changelog::with(['categories', 'contextualBanner'])
            ->withCount(['reactions', 'comments'])
            ->when($filters['search'] !== '', fn ($q) => $q->where('title', 'like', '%' . $filters['search'] . '%'))
            ->when(in_array($filters['type'], ['feature', 'hotfix', 'improvement', 'announcement'], true), fn ($q) => $q->where('type', $filters['type']))
            ->when(in_array($filters['status'], ['draft', 'published', 'archived'], true), fn ($q) => $q->where('status', $filters['status']))
            ->when($filters['from'] !== '', fn ($q) => $q->whereDate('published_at', '>=', $filters['from']))
            ->when($filters['to'] !== '', fn ($q) => $q->whereDate('published_at', '<=', $filters['to']))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('changelogs.index', compact('changelogs', 'filters'));
    }

    public function create()
    {
        return view('changelogs.form', [
            'changelog'  => new Changelog(['type' => 'feature', 'status' => 'draft', 'reaction_emoji' => '❤️']),
            'categories' => Category::orderBy('name')->get(),
            'selected'   => [],
            'comments'   => collect(),
            'reactions'  => collect(),
            'feedbacks'  => collect(),
            'banner'     => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $changelog = new Changelog($data['attributes']);
        $changelog->save();

        $this->afterSave($request, $changelog, $data);

        return redirect()->route('changelogs.index')->with('status', 'Changelog criado.');
    }

    public function edit(Changelog $changelog)
    {
        $changelog->load('media', 'widgetSettings', 'contextualBanner.rules');

        $comments  = $changelog->comments()->latest()->get();
        $reactions = $changelog->reactions()
            ->selectRaw('emoji, count(*) as total')
            ->groupBy('emoji')
            ->orderByDesc('total')
            ->get();
        $feedbacks = $changelog->feedbacks()->latest()->get();

        return view('changelogs.form', [
            'changelog'  => $changelog,
            'categories' => Category::orderBy('name')->get(),
            'selected'   => $changelog->categories->pluck('id')->all(),
            'comments'   => $comments,
            'reactions'  => $reactions,
            'feedbacks'  => $feedbacks,
            'banner'     => $changelog->contextualBanner,
        ]);
    }

    public function update(Request $request, Changelog $changelog)
    {
        $data = $this->validateData($request);
        $changelog->update($data['attributes']);

        $this->afterSave($request, $changelog, $data);

        return redirect()->route('changelogs.index')->with('status', 'Changelog atualizado.');
    }

    public function destroy(Changelog $changelog)
    {
        $this->deleteImages($changelog);
        $changelog->delete();
        WidgetCache::bump($changelog->account_id);

        return redirect()->route('changelogs.index')->with('status', 'Changelog removido.');
    }

    public function publish(Changelog $changelog)
    {
        $changelog->update([
            'status' => 'published',
            'published_at' => $changelog->published_at ?: now(),
        ]);
        WidgetCache::bump($changelog->account_id);

        return back()->with('status', 'Changelog publicado.');
    }

    public function archive(Changelog $changelog)
    {
        $changelog->update(['status' => 'archived']);
        WidgetCache::bump($changelog->account_id);

        return back()->with('status', 'Changelog arquivado.');
    }

    // ---- helpers ----

    protected function validateData(Request $request): array
    {
        $v = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:feature,hotfix,improvement,announcement'],
            'status' => ['required', 'in:draft,published,archived'],
            'reaction_emoji' => ['nullable', 'string', 'max:16'],
            'published_at' => ['nullable', 'date'],
            'categories' => ['array'],
            'categories.*' => ['integer'],
            'images.*' => ['image', 'max:4096'],
            'youtube_urls' => ['nullable', 'string'],
            'remove_media' => ['array'],
            // configs de widget
            'fire_webhook'     => ['nullable', 'boolean'],
            'show_comments'    => ['nullable', 'boolean'],
            'allow_comments'   => ['nullable', 'boolean'],
            'show_reactions'   => ['nullable', 'boolean'],
            'feedback_enabled' => ['nullable', 'boolean'],
            'cta_text' => ['nullable', 'string', 'max:255'],
            'cta_url' => ['nullable', 'url', 'max:255'],
            'cta_color' => ['nullable', 'string', 'max:30'],
            'cta_new_tab' => ['nullable', 'boolean'],
        ], [], ['title' => 'título', 'type' => 'tipo', 'status' => 'status']);

        // Agendamento: se publicado sem data, publica agora.
        $publishedAt = $v['published_at'] ?? null;
        if ($v['status'] === 'published' && ! $publishedAt) {
            $publishedAt = now();
        }

        // Banner contextual
        $bannerRaw   = $request->input('banner', []);
        $bannerRules = $request->input('banner_rules', []);

        $banner = null;
        if (is_array($bannerRaw)) {
            $validRules = [];
            foreach ($bannerRules as $rule) {
                if (empty($rule['pattern'])) continue;
                if (!in_array($rule['type'] ?? '', ['include', 'exclude'], true)) continue;
                if (!in_array($rule['mode'] ?? '', ['exact', 'contains', 'starts_with', 'regex'], true)) continue;
                if ($rule['mode'] === 'regex' && @preg_match('/' . $rule['pattern'] . '/', '') === false) continue;
                $validRules[] = [
                    'type'       => $rule['type'],
                    'match_mode' => $rule['mode'],
                    'pattern'    => substr($rule['pattern'], 0, 500),
                ];
            }
            $banner = [
                'enabled'              => (bool) ($bannerRaw['enabled'] ?? false),
                'style'                => in_array($bannerRaw['style'] ?? '', ['toast', 'top_bar', 'bottom_bar'], true) ? $bannerRaw['style'] : 'toast',
                'position'             => in_array($bannerRaw['position'] ?? '', ['bottom_right', 'bottom_left', 'top_right', 'top_left'], true) ? $bannerRaw['position'] : 'bottom_right',
                'frequency'            => in_array($bannerRaw['frequency'] ?? '', ['once_per_user', 'until_clicked', 'times_capped'], true) ? $bannerRaw['frequency'] : 'once_per_user',
                'frequency_cap'        => isset($bannerRaw['frequency_cap']) ? max(1, min(50, (int) $bannerRaw['frequency_cap'])) : null,
                'auto_dismiss_seconds' => isset($bannerRaw['auto_dismiss_seconds']) && $bannerRaw['auto_dismiss_seconds'] !== '' ? max(1, min(300, (int) $bannerRaw['auto_dismiss_seconds'])) : null,
                'expires_at'           => filled($bannerRaw['expires_at'] ?? '') ? $bannerRaw['expires_at'] : null,
                'custom_copy'          => substr(strip_tags($bannerRaw['custom_copy'] ?? ''), 0, 500) ?: null,
                'cta_text'             => substr($bannerRaw['cta_text'] ?? '', 0, 80) ?: null,
                'cta_url'              => filter_var($bannerRaw['cta_url'] ?? '', FILTER_VALIDATE_URL) ? $bannerRaw['cta_url'] : null,
                'cta_new_tab'          => (bool) ($bannerRaw['cta_new_tab'] ?? false),
                'rules'                => $validRules,
            ];
        }

        return [
            'attributes' => [
                'title' => $v['title'],
                'description' => HtmlSanitizer::clean($v['description'] ?? ''),
                'type' => $v['type'],
                'status' => $v['status'],
                'reaction_emoji' => $v['reaction_emoji'] ?: '❤️',
                'published_at' => $publishedAt,
            ],
            'categories' => $v['categories'] ?? [],
            'youtube_urls' => $v['youtube_urls'] ?? '',
            'remove_media' => $v['remove_media'] ?? [],
            'widget' => [
                'fire_webhook'     => $request->boolean('fire_webhook'),
                'show_comments'    => $request->boolean('show_comments'),
                'allow_comments'   => $request->boolean('allow_comments'),
                'show_reactions'   => $request->boolean('show_reactions'),
                'feedback_enabled' => $request->boolean('feedback_enabled'),
                'cta_text'         => $v['cta_text'] ?? null,
                'cta_url'          => $v['cta_url'] ?? null,
                'cta_color'        => $v['cta_color'] ?? null,
                'cta_new_tab'      => $request->boolean('cta_new_tab'),
            ],
            'banner' => $banner,
        ];
    }

    protected function afterSave(Request $request, Changelog $changelog, array $data): void
    {
        $changelog->categories()->sync($data['categories']);
        $changelog->widgetSettings()->updateOrCreate([], $data['widget']);

        // Remoção de mídias marcadas.
        if ($data['remove_media']) {
            $toRemove = $changelog->media()->whereIn('id', $data['remove_media'])->get();
            foreach ($toRemove as $media) {
                if ($media->type === 'image' && $media->path) {
                    Storage::disk('public')->delete($media->path);
                }
                $media->delete();
            }
        }

        // Novas imagens.
        $position = (int) $changelog->media()->max('position');
        foreach ($request->file('images', []) as $image) {
            $path = $image->store("changelogs/{$changelog->account_id}", 'public');
            $changelog->media()->create(['type' => 'image', 'path' => $path, 'position' => ++$position]);
        }

        // YouTube (uma URL por linha).
        foreach (preg_split('/\r\n|\r|\n/', $data['youtube_urls']) as $url) {
            $url = trim($url);
            if ($url !== '') {
                $changelog->media()->create(['type' => 'youtube', 'url' => $url, 'position' => ++$position]);
            }
        }

        // Banner contextual
        if ($data['banner'] !== null) {
            $bannerData = $data['banner'];
            $rules = $bannerData['rules'];
            unset($bannerData['rules']);

            $banner = $changelog->contextualBanner()->updateOrCreate([], $bannerData);
            $banner->rules()->delete();
            foreach ($rules as $rule) {
                $banner->rules()->create($rule);
            }
        }

        WidgetCache::bump($changelog->account_id);
    }

    protected function deleteImages(Changelog $changelog): void
    {
        foreach ($changelog->media()->where('type', 'image')->whereNotNull('path')->get() as $media) {
            Storage::disk('public')->delete($media->path);
        }
    }
}
