<?php

namespace App\Http\Controllers;

use App\Models\WidgetSetting;
use App\Support\Tenant;
use App\Support\WidgetCache;
use Illuminate\Http\Request;

class WidgetSettingController extends Controller
{
    public function edit()
    {
        $settings = WidgetSetting::firstOrCreate(
            ['account_id' => Tenant::id()],
            ['theme' => ['accent' => '#6c5ce7', 'dark' => false]]
        );

        return view('widget-settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $v = $request->validate([
            'button_text'      => ['required', 'string', 'max:120'],
            'button_icon'      => ['nullable', 'string', 'max:80'],
            'open_mode'        => ['required', 'in:side,dropdown'],
            'position'         => ['required', 'in:left,right'],
            'accent'           => ['nullable', 'string', 'max:30'],
            'dark'             => ['nullable', 'boolean'],
            'custom_css'       => ['nullable', 'string'],
            'webhook_url'      => ['nullable', 'url', 'max:255'],
            'feedback_enabled' => ['nullable', 'boolean'],
            'roadmap_enabled'  => ['nullable', 'boolean'],
        ], [], ['button_text' => 'texto do botão', 'open_mode' => 'modo de abertura', 'position' => 'posição']);

        $settings = WidgetSetting::firstOrCreate(['account_id' => Tenant::id()]);
        $settings->update([
            'button_text'      => $v['button_text'],
            'open_mode'        => $v['open_mode'],
            'position'         => $v['position'],
            'theme'            => [
                'accent'      => $v['accent'] ?: '#6c5ce7',
                'dark'        => $request->boolean('dark'),
                'button_icon' => $v['button_icon'] ?: null,
            ],
            'custom_css'       => $v['custom_css'] ?? null,
            'webhook_url'      => $v['webhook_url'] ?? null,
            'feedback_enabled' => $request->boolean('feedback_enabled'),
            'roadmap_enabled'  => $request->boolean('roadmap_enabled'),
        ]);

        WidgetCache::bump(Tenant::id());

        return back()->with('status', 'Configurações do widget salvas.');
    }
}
