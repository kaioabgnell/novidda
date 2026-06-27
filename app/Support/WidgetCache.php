<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Versionamento de cache do feed do widget por conta.
 *
 * Em vez de apagar chaves específicas (que dependem de query params), guardamos
 * um número de versão por conta e o embutimos na chave de cache. Publicar/editar
 * incrementa a versão, invalidando tudo de uma vez. Usa o cache nativo do Laravel.
 */
class WidgetCache
{
    public static function version(int $accountId): int
    {
        return (int) Cache::rememberForever(self::versionKey($accountId), fn () => 1);
    }

    public static function bump(int $accountId): void
    {
        $key = self::versionKey($accountId);
        Cache::forever($key, self::version($accountId) + 1);
    }

    public static function key(int $accountId, string $suffix): string
    {
        return "widget:{$accountId}:v" . self::version($accountId) . ":{$suffix}";
    }

    protected static function versionKey(int $accountId): string
    {
        return "widget:{$accountId}:version";
    }
}
