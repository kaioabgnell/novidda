<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Changelog;
use App\Models\ChangelogFeedback;
use App\Models\Comment;
use App\Models\ContextualBanner;
use App\Models\ContextualRule;
use App\Models\Reaction;
use App\Models\Read;
use App\Models\RoadmapComment;
use App\Models\RoadmapFeedback;
use App\Models\RoadmapItem;
use App\Models\WidgetEvent;
use App\Models\WidgetFeedback;
use App\Support\WidgetCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WidgetApiController extends Controller
{
    protected function account(Request $request): Account
    {
        return $request->attributes->get('account');
    }

    /**
     * Config visual do widget.
     * Cache server-side (1h) + ETag, mas sem max-age no browser para que
     * alterações no painel sejam refletidas imediatamente após o próximo
     * request (o browser valida com If-None-Match e recebe 304 se nada mudou).
     */
    public function config(Request $request): JsonResponse
    {
        $account = $this->account($request);

        $payload = Cache::remember(WidgetCache::key($account->id, 'config'), now()->addHour(), function () use ($account) {
            $s = $account->widgetSettings;
            $theme = $s->theme ?? [];
            return [
                'button_text'      => $s->button_text ?? 'Novidades',
                'button_icon'      => $theme['button_icon'] ?? null,
                'open_mode'        => $s->open_mode ?? 'side',
                'position'         => $s->position ?? 'right',
                'accent'           => $theme['accent'] ?? '#6c5ce7',
                'dark'             => (bool) ($theme['dark'] ?? false),
                'custom_css'       => $s->custom_css ?? '',
                'roadmap_enabled'  => (bool) ($s->roadmap_enabled ?? true),
                'feed_limit'       => (int) ($s->feed_limit ?? 5),
            ];
        });

        // no-cache: browser revalida em todo request; usa 304 se ETag bater.
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $etag = '"' . md5($json) . '"';

        if (trim((string) $request->header('If-None-Match')) === $etag) {
            return response()->json(null, 304)->header('ETag', $etag);
        }

        return response()->json($payload)
            ->header('ETag', $etag)
            ->header('Cache-Control', 'no-cache');
    }

    /**
     * Contagem de não-lidos + config básica de posicionamento do botão.
     * Não cacheado (específico por reader) mas inclui dados de layout para
     * o loader posicionar o botão corretamente sem esperar o painel abrir.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $account  = $this->account($request);
        $readerId = (string) $request->query('reader_id', '');
        $s        = $account->widgetSettings;
        $theme    = $s->theme ?? [];

        $liveIds  = Changelog::live()->pluck('id');
        $unreadIds = $liveIds->values()->toArray();

        if ($readerId !== '') {
            $readIds   = Read::where('reader_id', $readerId)->whereIn('changelog_id', $liveIds)->pluck('changelog_id');
            $unreadIds = $liveIds->diff($readIds)->values()->toArray();
        }

        $hasContextual = Cache::remember(
            WidgetCache::key($account->id, 'has_ctxb'),
            now()->addMinutes(5),
            function () {
                return ContextualBanner::active()
                    ->whereHas('changelog', fn ($q) => $q->where('status', 'published'))
                    ->exists();
            }
        );

        return response()->json([
            'count'                   => count($unreadIds),
            'unread_ids'              => $unreadIds,
            'position'                => $s->position ?? 'right',
            'open_mode'               => $s->open_mode ?? 'side',
            'accent'                  => $theme['accent'] ?? '#6c5ce7',
            'dark'                    => (bool) ($theme['dark'] ?? false),
            'button_icon'             => $theme['button_icon'] ?? null,
            'has_contextual_banners'  => $hasContextual,
        ])->header('Cache-Control', 'no-cache, private');
    }

    /** Feed de changelogs publicados — cache médio + ETag. */
    public function feed(Request $request): JsonResponse
    {
        $account = $this->account($request);
        $limit   = (int) ($account->widgetSettings->feed_limit ?? 5);

        $payload = Cache::remember(WidgetCache::key($account->id, 'feed'), now()->addMinutes(10), function () use ($limit) {
            return Changelog::live()
                ->with(['media', 'categories', 'widgetSettings', 'comments' => fn ($q) => $q->approved()->latest()])
                ->withCount('reactions')
                ->orderByDesc('published_at')
                ->limit($limit)
                ->get()
                ->map(fn (Changelog $c) => $this->serializeChangelog($c))
                ->all();
        });

        $this->recordEvent($account->id, null, $request->query('reader_id'), 'open');

        // no-cache + ETag: browser revalida em toda abertura do painel.
        // O Cache::remember acima ainda evita query no DB quando nada mudou.
        $json = json_encode(['items' => $payload], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $etag = '"' . md5($json) . '"';

        if (trim((string) $request->header('If-None-Match')) === $etag) {
            return response()->json(null, 304)->header('ETag', $etag);
        }

        return response()->json(['items' => $payload])
            ->header('ETag', $etag)
            ->header('Cache-Control', 'no-cache');
    }

    /** Marca changelogs como lidos para o leitor (zera o badge). */
    public function read(Request $request): JsonResponse
    {
        $account  = $this->account($request);
        $readerId = $this->readerId($request);
        $liveIds  = Changelog::live()->pluck('id');

        foreach ($liveIds as $id) {
            Read::updateOrCreate(
                ['reader_id' => $readerId, 'changelog_id' => $id],
                ['account_id' => $account->id, 'read_at' => now()]
            );
        }

        return response()->json(['ok' => true]);
    }

    /** Registra/atualiza a reação do leitor. */
    public function reaction(Request $request): JsonResponse
    {
        $account = $this->account($request);
        $data    = $request->validate([
            'changelog_id' => ['required', 'integer'],
            'emoji'        => ['required', 'string', 'max:16'],
        ]);

        $changelog = Changelog::live()->find($data['changelog_id']);
        abort_unless($changelog, 404);

        Reaction::updateOrCreate(
            ['changelog_id' => $changelog->id, 'reader_id' => $this->readerId($request)],
            ['emoji' => $data['emoji']]
        );

        WidgetCache::bump($account->id);
        $this->recordEvent($account->id, $changelog->id, $this->readerId($request), 'reaction');

        return response()->json(['ok' => true]);
    }

    /** Cria comentário pendente (moderação obrigatória). */
    public function comment(Request $request): JsonResponse
    {
        $account = $this->account($request);

        if (filled($request->input('website'))) {
            return response()->json(['ok' => true]);
        }

        $data = $request->validate([
            'changelog_id' => ['required', 'integer'],
            'author_name'  => ['nullable', 'string', 'max:80'],
            'body'         => ['required', 'string', 'max:2000'],
        ]);

        $changelog = Changelog::live()->find($data['changelog_id']);
        abort_unless($changelog && ($changelog->widgetSettings->allow_comments ?? true), 404);

        Comment::create([
            'changelog_id' => $changelog->id,
            'reader_id'    => $this->readerId($request),
            'author_name'  => $data['author_name'] ?? null,
            'body'         => $data['body'],
            'status'       => 'pending',
        ]);

        $this->recordEvent($account->id, $changelog->id, $this->readerId($request), 'comment');

        return response()->json(['ok' => true, 'pending' => true]);
    }

    /** Registra o feedback (satisfação) do leitor para um changelog. Um por leitor por changelog. */
    public function changelogFeedback(Request $request): JsonResponse
    {
        $account  = $this->account($request);
        $readerId = $this->readerId($request);

        $data = $request->validate([
            'changelog_id' => ['required', 'integer'],
            'score'        => ['required', 'string', 'in:sad,neutral,happy'],
            'comment'      => ['nullable', 'string', 'max:2000'],
        ]);

        $changelog = Changelog::live()->find($data['changelog_id']);
        abort_unless($changelog, 404);
        abort_unless($changelog->widgetSettings->feedback_enabled ?? false, 403);

        $exists = ChangelogFeedback::where('changelog_id', $changelog->id)
            ->where('reader_id', $readerId)
            ->exists();

        if ($exists) {
            return response()->json(['ok' => true, 'already_submitted' => true]);
        }

        ChangelogFeedback::create([
            'changelog_id' => $changelog->id,
            'reader_id'    => $readerId,
            'score'        => $data['score'],
            'comment'      => $data['comment'] ?? null,
        ]);

        $this->recordEvent($account->id, $changelog->id, $readerId, 'feedback');

        return response()->json(['ok' => true, 'created' => true]);
    }

    /** Feed de itens de roadmap publicados. */
    public function roadmapFeed(Request $request): JsonResponse
    {
        $account = $this->account($request);

        $payload = Cache::remember(WidgetCache::key($account->id, 'roadmap'), now()->addMinutes(10), function () {
            return RoadmapItem::published()
                ->withCount('feedbacks')
                ->orderByDesc('published_at')
                ->limit(30)
                ->get()
                ->map(fn (RoadmapItem $item) => [
                    'id'               => $item->id,
                    'title'            => $item->title,
                    'description'      => $item->description,
                    'status'           => $item->status,
                    'feedback_enabled' => (bool) $item->feedback_enabled,
                    'feedbacks_count'  => $item->feedbacks_count,
                    'published_at'     => $item->published_at?->toIso8601String(),
                ])
                ->all();
        });

        $json = json_encode(['items' => $payload], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $etag = '"' . md5($json) . '"';

        if (trim((string) $request->header('If-None-Match')) === $etag) {
            return response()->json(null, 304)->header('ETag', $etag);
        }

        return response()->json(['items' => $payload])
            ->header('ETag', $etag)
            ->header('Cache-Control', 'no-cache');
    }

    /** Registra feedback em item de roadmap. Um por leitor por item. */
    public function roadmapFeedback(Request $request): JsonResponse
    {
        $readerId = $this->readerId($request);

        $data = $request->validate([
            'roadmap_item_id' => ['required', 'integer'],
            'score'           => ['required', 'string', 'in:sad,neutral,happy'],
            'comment'         => ['nullable', 'string', 'max:2000'],
        ]);

        $item = RoadmapItem::published()->find($data['roadmap_item_id']);
        abort_unless($item, 404);
        abort_unless($item->feedback_enabled, 403);

        $exists = RoadmapFeedback::where('roadmap_item_id', $item->id)
            ->where('reader_id', $readerId)
            ->exists();

        if ($exists) {
            return response()->json(['ok' => true, 'already_submitted' => true]);
        }

        RoadmapFeedback::create([
            'roadmap_item_id' => $item->id,
            'reader_id'       => $readerId,
            'score'           => $data['score'],
            'comment'         => $data['comment'] ?? null,
        ]);

        WidgetCache::bump($this->account($request)->id);

        return response()->json(['ok' => true, 'created' => true]);
    }

    /** Cria comentário pendente em item de roadmap. */
    public function roadmapComment(Request $request): JsonResponse
    {
        if (filled($request->input('website'))) {
            return response()->json(['ok' => true]);
        }

        $data = $request->validate([
            'roadmap_item_id' => ['required', 'integer'],
            'author_name'     => ['nullable', 'string', 'max:80'],
            'body'            => ['required', 'string', 'max:2000'],
        ]);

        $item = RoadmapItem::published()->find($data['roadmap_item_id']);
        abort_unless($item, 404);

        RoadmapComment::create([
            'roadmap_item_id' => $item->id,
            'reader_id'       => $this->readerId($request),
            'author_name'     => $data['author_name'] ?? null,
            'body'            => $data['body'],
            'status'          => 'pending',
        ]);

        return response()->json(['ok' => true, 'pending' => true]);
    }

    /** Lista banners contextuais ativos — cacheado 5 min + ETag. */
    public function contextual(Request $request): JsonResponse
    {
        $account = $this->account($request);

        $payload = Cache::remember(WidgetCache::key($account->id, 'contextual'), now()->addMinutes(5), function () {
            return ContextualBanner::active()
                ->whereHas('changelog', fn ($q) => $q->where('status', 'published'))
                ->with(['changelog', 'rules'])
                ->get()
                ->map(fn (ContextualBanner $b) => $this->serializeBanner($b))
                ->all();
        });

        $json = json_encode(['banners' => $payload], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $etag = '"' . md5($json) . '"';

        if (trim((string) $request->header('If-None-Match')) === $etag) {
            return response()->json(null, 304)->header('ETag', $etag);
        }

        return response()->json(['banners' => $payload])
            ->header('ETag', $etag)
            ->header('Cache-Control', 'no-cache');
    }

    /** Registra evento de banner contextual (shown / dismissed / clicked). */
    public function contextualEvent(Request $request): \Illuminate\Http\Response
    {
        $account = $this->account($request);

        $data = $request->validate([
            'banner_id' => ['required', 'integer'],
            'reader_id' => ['required', 'string', 'max:64'],
            'event'     => ['required', 'in:shown,dismissed,clicked'],
            'url'       => ['nullable', 'string', 'max:1000'],
        ]);

        // Verifica que o banner pertence à conta (segurança)
        $banner = ContextualBanner::whereHas('changelog', fn ($q) => $q->where('status', 'published'))
            ->find($data['banner_id']);

        if (!$banner) {
            return response()->noContent();
        }

        WidgetEvent::create([
            'account_id'   => $account->id,
            'changelog_id' => $banner->changelog_id,
            'reader_id'    => substr($data['reader_id'], 0, 64),
            'type'         => 'contextual_' . $data['event'],
            'metadata'     => ['banner_id' => $banner->id, 'url' => $data['url'] ?? null],
            'created_at'   => now(),
        ]);

        return response()->noContent();
    }

    // ---- helpers ----

    protected function serializeChangelog(Changelog $c): array
    {
        $ws = $c->widgetSettings;
        return [
            'id'              => $c->id,
            'title'           => $c->title,
            'description'     => $c->description,
            'type'            => $c->type,
            'published_at'    => $c->published_at?->toIso8601String(),
            'reaction_emoji'  => $c->reaction_emoji,
            'reactions_count' => $c->reactions_count,
            'categories'      => $c->categories->map(fn ($cat) => [
                'name'  => $cat->name,
                'color' => $cat->color,
                'icon'  => $cat->icon,
            ])->all(),
            'media'           => $c->media->map(fn ($m) => [
                'type'       => $m->type,
                'url'        => $m->display_url,
                'youtube_id' => $m->type === 'youtube' ? $this->youtubeId($m->url) : null,
            ])->all(),
            'settings'        => [
                'show_reactions'   => (bool) ($ws->show_reactions ?? true),
                'show_comments'    => (bool) ($ws->show_comments ?? true),
                'allow_comments'   => (bool) ($ws->allow_comments ?? true),
                'feedback_enabled' => (bool) ($ws->feedback_enabled ?? false),
                'cta_text'         => $ws->cta_text ?? null,
                'cta_url'          => $ws->cta_url ?? null,
                'cta_color'        => $ws->cta_color ?? null,
                'cta_new_tab'      => (bool) ($ws->cta_new_tab ?? true),
            ],
            'comments'        => ($ws->show_comments ?? true)
                ? $c->comments->map(fn ($cm) => [
                    'author_name' => $cm->author_name ?: 'Anônimo',
                    'body'        => $cm->body,
                    'created_at'  => $cm->created_at->toIso8601String(),
                ])->all()
                : [],
        ];
    }

    protected function serializeBanner(ContextualBanner $b): array
    {
        $c = $b->changelog;
        $typeIcons = [
            'feature'      => 'fa-solid fa-star',
            'hotfix'       => 'fa-solid fa-wrench',
            'improvement'  => 'fa-solid fa-arrow-trend-up',
            'announcement' => 'fa-solid fa-bullhorn',
        ];
        $includeRules = $b->rules->where('type', 'include')->values();
        $excludeRules = $b->rules->where('type', 'exclude')->values();

        return [
            'id'                    => $b->id,
            'changelog_id'          => $b->changelog_id,
            'style'                 => $b->style,
            'position'              => $b->position,
            'frequency'             => $b->frequency,
            'frequency_cap'         => $b->frequency_cap,
            'auto_dismiss_seconds'  => $b->auto_dismiss_seconds,
            'expires_at'            => $b->expires_at?->toIso8601String(),
            'copy' => [
                'title'       => $b->custom_copy ?: $c->title,
                'description' => $this->excerpt($c->description ?? '', 120),
                'icon'        => $typeIcons[$c->type] ?? null,
            ],
            'cta' => $b->cta_text ? [
                'text'    => $b->cta_text,
                'url'     => $b->cta_url ?? '#',
                'new_tab' => (bool) $b->cta_new_tab,
            ] : null,
            'rules' => [
                'include' => $includeRules->map(fn ($r) => ['mode' => $r->match_mode, 'pattern' => $r->pattern])->all(),
                'exclude' => $excludeRules->map(fn ($r) => ['mode' => $r->match_mode, 'pattern' => $r->pattern])->all(),
            ],
        ];
    }

    protected function excerpt(string $html, int $max): string
    {
        $text = trim(strip_tags($html));
        return mb_strlen($text) > $max ? mb_substr($text, 0, $max) . '…' : $text;
    }

    protected function youtubeId(?string $url): ?string
    {
        if (! $url) return null;
        if (preg_match('#(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|v/|shorts/))([\w-]{11})#', $url, $m)) {
            return $m[1];
        }
        return null;
    }

    protected function readerId(Request $request): string
    {
        $id = (string) ($request->input('reader_id') ?: $request->query('reader_id') ?: '');
        return $id !== '' ? substr($id, 0, 64) : 'anon-' . substr(md5($request->ip() . $request->userAgent()), 0, 16);
    }

    protected function recordEvent(int $accountId, ?int $changelogId, $readerId, string $type): void
    {
        WidgetEvent::create([
            'account_id'   => $accountId,
            'changelog_id' => $changelogId,
            'reader_id'    => $readerId ? substr((string) $readerId, 0, 64) : null,
            'type'         => $type,
            'created_at'   => now(),
        ]);
    }

    protected function cached(Request $request, array $payload, int $maxAge): JsonResponse
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $etag = '"' . md5($json) . '"';

        if (trim((string) $request->header('If-None-Match')) === $etag) {
            return response()->json(null, 304)->header('ETag', $etag);
        }

        return response()->json($payload)
            ->header('ETag', $etag)
            ->header('Cache-Control', "public, max-age={$maxAge}");
    }
}
