<?php

namespace App\Http\Controllers;

use App\Models\AttributeDiscoveryCache;
use App\Models\Changelog;
use App\Models\ChangelogSegmentRule;
use App\Models\UserAttributeIndex;
use App\Services\SegmentMatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SegmentationController extends Controller
{
    /** Lista atributos canônicos + descobertos automaticamente para popular o dropdown. */
    public function discovery(): JsonResponse
    {
        $account = auth()->user()->account;

        $total = UserAttributeIndex::where('account_id', $account->id)
            ->where('last_seen_at', '>', now()->subDays(30))
            ->count();

        $discovered = AttributeDiscoveryCache::where('account_id', $account->id)
            ->orderBy('coverage_count', 'desc')
            ->get()
            ->map(fn ($a) => [
                'path'          => $a->attribute_path,
                'type'          => $a->detected_type,
                'coverage_pct'  => $total > 0 ? round($a->coverage_count / $total * 100) : 0,
                'sample_values' => $a->sample_values ?? [],
            ]);

        return response()->json([
            'canonical'       => $this->canonicalAttributes(),
            'discovered'      => $discovered,
            'total_users_30d' => $total,
        ]);
    }

    /** Estimativa de alcance em tempo real para um conjunto de regras. */
    public function estimateReach(Request $request, Changelog $changelog): JsonResponse
    {
        $rules = $request->input('rules', []);

        $totalActive = UserAttributeIndex::where('account_id', $changelog->account_id)
            ->where('last_seen_at', '>', now()->subDays(30))
            ->count();

        if (empty($rules)) {
            return response()->json([
                'matched'    => $totalActive,
                'total'      => $totalActive,
                'percentage' => 100,
            ]);
        }

        $matcher = new SegmentMatcher();

        $matched = UserAttributeIndex::where('account_id', $changelog->account_id)
            ->where('last_seen_at', '>', now()->subDays(30))
            ->get()
            ->filter(fn ($u) => $matcher->matchesRaw($u->attributes_snapshot ?? [], $rules))
            ->count();

        return response()->json([
            'matched'    => $matched,
            'total'      => $totalActive,
            'percentage' => $totalActive > 0 ? round($matched / $totalActive * 100) : 0,
        ]);
    }

    /** Lista paginada de usuários que atendem às regras (pré-visualização). */
    public function previewAudience(Request $request, Changelog $changelog): JsonResponse
    {
        $rules = $request->input('rules', []);

        $matcher = new SegmentMatcher();

        $all = UserAttributeIndex::where('account_id', $changelog->account_id)
            ->where('last_seen_at', '>', now()->subDays(30))
            ->get();

        $matched = empty($rules)
            ? $all
            : $all->filter(fn ($u) => $matcher->matchesRaw($u->attributes_snapshot ?? [], $rules));

        $total   = $matched->count();
        $perPage = 15;
        $page    = max(1, (int) $request->query('page', 1));

        $items = $matched->values()
            ->slice(($page - 1) * $perPage, $perPage)
            ->map(fn ($u) => [
                'reader_id'   => $u->reader_id,
                'snapshot'    => $u->attributes_snapshot,
                'last_seen'   => $u->last_seen_at?->toIso8601String(),
            ])
            ->values();

        return response()->json([
            'total'       => $total,
            'per_page'    => $perPage,
            'current_page'=> $page,
            'last_page'   => max(1, (int) ceil($total / $perPage)),
            'items'       => $items,
        ]);
    }

    private function canonicalAttributes(): array
    {
        return [
            ['path' => 'plan',         'type' => 'string'],
            ['path' => 'role',         'type' => 'string'],
            ['path' => 'id',           'type' => 'string'],
            ['path' => 'email',        'type' => 'string'],
            ['path' => 'name',         'type' => 'string'],
            ['path' => 'created_at',   'type' => 'date'],
            ['path' => 'company.id',   'type' => 'string'],
            ['path' => 'company.name', 'type' => 'string'],
            ['path' => 'company.plan', 'type' => 'string'],
        ];
    }
}
