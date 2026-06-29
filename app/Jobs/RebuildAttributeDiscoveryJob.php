<?php

namespace App\Jobs;

use App\Models\Account;
use App\Models\AttributeDiscoveryCache;
use App\Models\UserAttributeIndex;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RebuildAttributeDiscoveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Account::chunk(50, function ($accounts) {
            foreach ($accounts as $account) {
                $this->rebuildForAccount($account);
            }
        });
    }

    private function rebuildForAccount(Account $account): void
    {
        $records = UserAttributeIndex::where('account_id', $account->id)
            ->where('last_seen_at', '>', now()->subDays(30))
            ->get();

        if ($records->isEmpty()) {
            return;
        }

        $paths = [];
        foreach ($records as $record) {
            $flat = $this->flatten($record->attributes_snapshot ?? []);
            foreach ($flat as $path => $value) {
                if (!isset($paths[$path])) {
                    $paths[$path] = [
                        'count'   => 0,
                        'type'    => $this->detectType($value),
                        'samples' => [],
                    ];
                }
                $paths[$path]['count']++;
                if (count($paths[$path]['samples']) < 20
                    && !in_array($value, $paths[$path]['samples'], true)) {
                    $paths[$path]['samples'][] = $value;
                }
            }
        }

        foreach ($paths as $path => $meta) {
            AttributeDiscoveryCache::updateOrCreate(
                ['account_id' => $account->id, 'attribute_path' => $path],
                [
                    'detected_type'  => $meta['type'],
                    'coverage_count' => $meta['count'],
                    'sample_values'  => array_slice($meta['samples'], 0, 20),
                    'updated_at'     => now(),
                ]
            );
        }
    }

    private function flatten(array $data, string $prefix = ''): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $path = $prefix ? "{$prefix}.{$key}" : $key;
            if (is_array($value) && $this->isAssoc($value)) {
                $result += $this->flatten($value, $path);
            } else {
                $result[$path] = $value;
            }
        }

        return $result;
    }

    private function isAssoc(array $arr): bool
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    private function detectType($value): string
    {
        if (is_bool($value)) return 'boolean';
        if (is_numeric($value)) return 'number';
        if (is_array($value)) return 'array';
        if (is_string($value) && $this->isIsoDate($value)) return 'date';

        return 'string';
    }

    private function isIsoDate(string $value): bool
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}(T\d{2}:\d{2}(:\d{2})?)?/', $value);
    }
}
