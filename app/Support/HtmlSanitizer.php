<?php

namespace App\Support;

/**
 * Sanitizador leve para o HTML produzido pelo editor Quill.
 *
 * Estratégia: allowlist de tags + remoção de atributos de evento (on*),
 * de URLs perigosas (javascript:) e de tags script/style. Cobre o subconjunto
 * que o Quill gera. Não é um HTMLPurifier completo, mas é seguro para esse uso
 * controlado (entrada vem do próprio dono da conta autenticado).
 */
class HtmlSanitizer
{
    protected const ALLOWED_TAGS = '<p><br><strong><em><u><s><h1><h2><h3><blockquote><ol><ul><li><a><img><pre><code><span>';

    public static function clean(?string $html): string
    {
        if (blank($html)) {
            return '';
        }

        // Remove blocos script/style inteiros.
        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $html);

        // Mantém só as tags permitidas.
        $html = strip_tags($html, self::ALLOWED_TAGS);

        // Remove atributos de evento (onclick, onerror, ...).
        $html = preg_replace('#\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html);

        // Neutraliza URLs javascript:/data: em href/src.
        $html = preg_replace('#(href|src)\s*=\s*("|\')\s*(javascript|data|vbscript):[^"\']*\2#i', '$1="#"', $html);

        return trim($html);
    }
}
