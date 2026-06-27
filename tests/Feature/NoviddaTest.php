<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Changelog;
use App\Models\User;
use App\Models\WidgetSetting;
use App\Support\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NoviddaTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Tenant::clear();
        parent::tearDown();
    }

    /** Cadastro cria conta (workspace) + usuário owner + config de widget. */
    public function test_registro_cria_conta_e_owner(): void
    {
        $response = $this->post('/register', [
            'company' => 'Minha Empresa',
            'name' => 'Fulano',
            'email' => 'fulano@example.com',
            'password' => 'segredo123',
            'password_confirmation' => 'segredo123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'fulano@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('owner', $user->role);
        $this->assertNotNull($user->account_id);
        $this->assertDatabaseHas('widget_settings', ['account_id' => $user->account_id]);
        $this->assertNotEmpty($user->account->widget_token);
    }

    public function test_visitante_e_redirecionado_para_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    /** Isolamento multi-tenant: uma conta não enxerga changelogs de outra. */
    public function test_isolamento_multi_tenant_no_painel(): void
    {
        [$userA] = $this->makeAccountWithChangelog('Empresa A', 'Release da A');
        [$userB] = $this->makeAccountWithChangelog('Empresa B', 'Release da B');

        Tenant::clear();

        $this->actingAs($userA)->get('/changelogs')
            ->assertOk()
            ->assertSee('Release da A')
            ->assertDontSee('Release da B');
    }

    /** Feed do widget retorna só changelogs live (exclui rascunho e agendado futuro). */
    public function test_feed_do_widget_retorna_apenas_live(): void
    {
        $account = Account::create(['name' => 'Empresa Widget']);
        WidgetSetting::create(['account_id' => $account->id, 'theme' => ['accent' => '#000', 'dark' => false]]);
        Tenant::set($account->id);

        Changelog::create(['title' => 'Publicado agora', 'type' => 'feature', 'status' => 'published', 'published_at' => now()->subDay()]);
        Changelog::create(['title' => 'Rascunho', 'type' => 'feature', 'status' => 'draft']);
        Changelog::create(['title' => 'Agendado futuro', 'type' => 'feature', 'status' => 'published', 'published_at' => now()->addWeek()]);

        Tenant::clear();

        $response = $this->getJson("/api/v1/widget/{$account->widget_token}/feed");

        $response->assertOk()
            ->assertJsonCount(1, 'items')
            ->assertJsonFragment(['title' => 'Publicado agora'])
            ->assertJsonMissing(['title' => 'Rascunho'])
            ->assertJsonMissing(['title' => 'Agendado futuro']);
    }

    public function test_token_invalido_retorna_404(): void
    {
        $this->getJson('/api/v1/widget/token-inexistente/feed')->assertNotFound();
    }

    /** Comentário do widget entra como pendente (moderação obrigatória). */
    public function test_comentario_entra_pendente(): void
    {
        $account = Account::create(['name' => 'Empresa C']);
        WidgetSetting::create(['account_id' => $account->id]);
        Tenant::set($account->id);
        $changelog = Changelog::create(['title' => 'Com comentários', 'type' => 'feature', 'status' => 'published', 'published_at' => now()->subDay()]);
        $changelog->widgetSettings()->create(['allow_comments' => true, 'show_comments' => true]);
        Tenant::clear();

        $this->postJson("/api/v1/widget/{$account->widget_token}/comment", [
            'changelog_id' => $changelog->id,
            'author_name' => 'Visitante',
            'body' => 'Muito bom!',
        ])->assertOk()->assertJson(['pending' => true]);

        $this->assertDatabaseHas('comments', ['body' => 'Muito bom!', 'status' => 'pending']);
    }

    private function makeAccountWithChangelog(string $company, string $title): array
    {
        $account = Account::create(['name' => $company]);
        $user = User::create([
            'account_id' => $account->id,
            'name' => 'User ' . $company,
            'email' => str()->slug($company) . '@example.com',
            'password' => Hash::make('segredo123'),
            'role' => 'owner',
        ]);
        Tenant::set($account->id);
        Changelog::create(['title' => $title, 'type' => 'feature', 'status' => 'published', 'published_at' => now()->subDay()]);

        return [$user, $account];
    }
}
