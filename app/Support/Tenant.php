<?php

namespace App\Support;

/**
 * Contexto de tenant da requisição atual.
 *
 * - No painel admin, é setado a partir do usuário autenticado (middleware).
 * - Na API do widget, é setado a partir do widget_token resolvido.
 *
 * Os models que usam o trait BelongsToAccount leem este id para escopar
 * automaticamente as queries e preencher account_id ao criar.
 */
class Tenant
{
    protected static ?int $accountId = null;

    public static function set(?int $accountId): void
    {
        static::$accountId = $accountId;
    }

    public static function id(): ?int
    {
        return static::$accountId;
    }

    public static function check(): bool
    {
        return static::$accountId !== null;
    }

    public static function clear(): void
    {
        static::$accountId = null;
    }
}
