<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Category;
use App\Models\Changelog;
use App\Models\ChangelogWidgetSetting;
use App\Models\User;
use App\Models\WidgetSetting;
use App\Support\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $account = Account::create(['name' => 'Demo Operand']);

        User::create([
            'account_id' => $account->id,
            'name' => 'Kaio Gomes',
            'email' => 'kaio.gomes@operand.com.br',
            'password' => Hash::make('password'),
            'role' => 'owner',
        ]);

        // A partir daqui, escopa o tenant para os models preencherem account_id sozinhos.
        Tenant::set($account->id);

        WidgetSetting::create([
            'account_id' => $account->id,
            'theme' => ['accent' => '#6c5ce7', 'dark' => false],
        ]);

        $feature = Category::create(['name' => 'Novidade', 'color' => '#6c5ce7', 'icon' => 'fa-solid fa-star']);
        $fix = Category::create(['name' => 'Correção', 'color' => '#e17055', 'icon' => 'fa-solid fa-wrench']);

        $changelogs = [
            ['title' => 'Lançamento do Novidda', 'type' => 'announcement', 'status' => 'published'],
            ['title' => 'Modo escuro no painel', 'type' => 'feature', 'status' => 'published'],
            ['title' => 'Correção no contador de não-lidos', 'type' => 'hotfix', 'status' => 'draft'],
        ];

        foreach ($changelogs as $data) {
            $changelog = Changelog::create([
                'title' => $data['title'],
                'description' => '<p>Descrição de exemplo para <strong>' . $data['title'] . '</strong>.</p>',
                'type' => $data['type'],
                'status' => $data['status'],
                'published_at' => $data['status'] === 'published' ? now() : null,
            ]);

            $changelog->categories()->attach($data['type'] === 'hotfix' ? $fix->id : $feature->id);

            ChangelogWidgetSetting::create(['changelog_id' => $changelog->id]);
        }

        Tenant::clear();
    }
}
