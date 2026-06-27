<?php

namespace App\Models\Concerns;

use App\Models\Account;
use App\Support\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Isolamento multi-tenant automático.
 *
 * - Global scope: filtra por account_id sempre que houver um tenant no contexto.
 *   Sem tenant setado, não filtra (ex: jobs/console) — nesses casos escope manualmente.
 * - Ao criar um model, preenche account_id com o tenant atual se estiver vazio.
 */
trait BelongsToAccount
{
    public static function bootBelongsToAccount(): void
    {
        static::addGlobalScope('account', function (Builder $builder) {
            if (Tenant::check()) {
                $builder->where($builder->getModel()->getTable() . '.account_id', Tenant::id());
            }
        });

        static::creating(function ($model) {
            if (empty($model->account_id) && Tenant::check()) {
                $model->account_id = Tenant::id();
            }
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /** Remove o escopo de tenant para uma query específica (uso deliberado). */
    public function scopeWithoutTenant(Builder $query): Builder
    {
        return $query->withoutGlobalScope('account');
    }
}
