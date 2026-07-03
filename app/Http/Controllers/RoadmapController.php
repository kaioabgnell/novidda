<?php

namespace App\Http\Controllers;

use App\Models\RoadmapItem;
use App\Support\HtmlSanitizer;
use App\Support\WidgetCache;
use Illuminate\Http\Request;

class RoadmapController extends Controller
{
    public function index()
    {
        $items = RoadmapItem::withCount([
            'feedbacks',
            'comments',
            'votes as votes_up_count'   => fn ($q) => $q->where('vote', 'up'),
            'votes as votes_down_count' => fn ($q) => $q->where('vote', 'down'),
        ])
            ->latest()
            ->paginate(20);

        return view('roadmap.index', compact('items'));
    }

    public function create()
    {
        return view('roadmap.form', [
            'item'      => new RoadmapItem(['status' => 'analyzing', 'feedback_enabled' => true, 'voting_enabled' => false]),
            'feedbacks' => collect(),
            'comments'  => collect(),
            'votesUp'   => 0,
            'votesDown' => 0,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $item = RoadmapItem::create($data);
        WidgetCache::bump($item->account_id);

        return redirect()->route('roadmap.index')->with('status', 'Item de roadmap criado.');
    }

    public function edit(RoadmapItem $roadmap)
    {
        $feedbacks = $roadmap->feedbacks()->latest()->get();
        $comments  = $roadmap->comments()->latest()->get();
        $votesUp   = $roadmap->votes()->where('vote', 'up')->count();
        $votesDown = $roadmap->votes()->where('vote', 'down')->count();

        return view('roadmap.form', compact('roadmap', 'feedbacks', 'comments', 'votesUp', 'votesDown') + ['item' => $roadmap]);
    }

    public function update(Request $request, RoadmapItem $roadmap)
    {
        $data = $this->validated($request);
        $roadmap->update($data);
        WidgetCache::bump($roadmap->account_id);

        return redirect()->route('roadmap.index')->with('status', 'Item de roadmap atualizado.');
    }

    public function destroy(RoadmapItem $roadmap)
    {
        WidgetCache::bump($roadmap->account_id);
        $roadmap->delete();

        return redirect()->route('roadmap.index')->with('status', 'Item removido.');
    }

    protected function validated(Request $request): array
    {
        $v = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'status'           => ['required', 'in:analyzing,developing,planned'],
            'feedback_enabled' => ['nullable', 'boolean'],
            'voting_enabled'   => ['nullable', 'boolean'],
            'published_at'     => ['nullable', 'date'],
        ], [], ['title' => 'título', 'status' => 'andamento']);

        return [
            'title'            => $v['title'],
            'description'      => HtmlSanitizer::clean($v['description'] ?? ''),
            'status'           => $v['status'],
            'feedback_enabled' => $request->boolean('feedback_enabled'),
            'voting_enabled'   => $request->boolean('voting_enabled'),
            'published_at'     => $v['published_at'] ?? now(),
        ];
    }
}
