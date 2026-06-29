<?php

namespace App\Jobs;

use App\Models\UserAttributeIndex;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IndexUserAttributesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $accountId,
        public readonly array $user
    ) {}

    public function handle(): void
    {
        // Remove PII direta antes de armazenar
        $snapshot = $this->user;
        unset($snapshot['email'], $snapshot['name']);

        UserAttributeIndex::updateOrCreate(
            [
                'account_id' => $this->accountId,
                'reader_id'  => substr((string) ($this->user['id'] ?? 'anon'), 0, 64),
            ],
            [
                'attributes_snapshot' => $snapshot,
                'last_seen_at'        => now(),
            ]
        );
    }
}
