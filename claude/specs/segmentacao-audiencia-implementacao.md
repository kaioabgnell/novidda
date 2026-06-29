# Novidda — Segmentação de Audiência

> Especificação completa de implementação — MVP e Fase 2
> Configuração **por changelog** com layout em abas dentro do formulário de edição
> Atributos canônicos + atributos livres com auto-descoberta

---

## 1. Visão geral

### 1.1 O que é

A segmentação de audiência permite que cada changelog defina **quem pode ver** entre os usuários do sistema do cliente. Em vez de todo anúncio chegar a todos, o autor configura regras baseadas em atributos do usuário — plano, papel, data de cadastro, ou qualquer atributo customizado que o cliente passe ao widget.

### 1.2 Princípios de design

- **Configuração por changelog.** Cada release tem suas próprias regras, sem herdar de uma configuração global. Default é "todos veem".
- **Layout em abas.** Para não inchar o formulário principal, a segmentação fica numa aba separada (`Conteúdo | Widget | Segmentação`).
- **Contrato híbrido de atributos.** Campos canônicos no nível raiz (`plan`, `role`, etc.) + objeto `attributes` totalmente livre.
- **Falha graciosa.** Usuário anônimo, atributo ausente, regra inválida — nada disso quebra o widget.
- **Matching no servidor.** Regras nunca vazam para o client. Cache estratégico por "shape" de usuário.
- **Auto-descoberta de atributos.** Painel detecta automaticamente quais atributos os usuários estão enviando.

### 1.3 Posicionamento estratégico

Combinada com o banner contextual, a segmentação dá ao Novidda **inteligência dupla**: onde o anúncio aparece (URL) e para quem aparece (atributos do usuário). Esse é o nível de capacidade que justifica posicionamento premium no mercado SaaS — substitui a necessidade de ferramentas como Pendo e Userflow para casos de comunicação.

### 1.4 O que está dentro e fora do escopo

**Dentro do escopo:**
- Modelo de dados de atributos (raiz canônica + `attributes` livre).
- Regras por changelog com lógica AND entre múltiplas regras.
- Operadores: equals, not_equals, contains, starts_with, ends_with, greater_than, less_than, before, after, in, not_in, exists, not_exists.
- Suporte a dot-notation em atributos (`attributes.industry`, `company.plan`).
- Matching no servidor com cache estratégico.
- Auto-descoberta de atributos no admin.
- Estimativa de alcance em tempo real.
- Pré-visualização de quem vê.

**Fora do escopo (Fase 3):**
- Lógica OR e grupos de regras combinados (AND/OR aninhado).
- Identity verification via HMAC.
- Targeting cross-account (segmentação compartilhada entre múltiplos changelogs).
- A/B testing de versões de changelog.

---

## 2. O contrato de dados de usuário

### 2.1 Estrutura completa

Como o widget é injetado no sistema do cliente, ele recebe os dados do usuário logado via `window.noviddaConfig`. O contrato segue padrão híbrido — campos canônicos no nível raiz, atributos livres dentro de `attributes`.

```javascript
window.noviddaConfig = {
  token: 'acc_a8f3k2...',  // ÚNICO obrigatório (identifica a conta Novidda)

  user: {  // tudo opcional - se ausente, usuário é anônimo

    // ─── CAMPOS CANÔNICOS DO NOVIDDA ───
    id: 'user_12345',
    email: 'ana@empresa.com.br',
    name: 'Ana Silva',
    plan: 'pro',
    role: 'admin',
    created_at: '2024-03-15',

    company: {
      id: 'company_789',
      name: 'Operand',
      plan: 'enterprise'
    },

    // ─── ATRIBUTOS LIVRES ───
    attributes: {
      industry: 'agência',
      city: 'São Paulo',
      employees: 87,
      beta_program: true,
      onboarding_completed: true,
      mrr: 1500,
      tags: ['vip', 'early_adopter'],
      last_purchase: '2026-06-12'
    }
  }
};
```

### 2.2 Justificativa de cada campo canônico

Cada campo no nível raiz tem função explícita no produto e paga seu lugar:

| Campo | Tipo | Função |
|---|---|---|
| `id` | string\|number | Identifica usuário entre sessões. Necessário para "lido/não-lido", reações, comentários. |
| `email` | string | Aparece em moderação de comentários. Permite contato futuro fora do widget. |
| `name` | string | Substitui "Visitante" em comentários públicos. |
| `plan` | string | Atributo de segmentação mais comum em SaaS. Padronizar a chave evita inconsistência. |
| `role` | string | Segundo atributo mais comum (admin / membro / viewer). |
| `created_at` | ISO 8601 | Permite regras temporais ("usuários antigos") sem o cliente precisar duplicar. |
| `company` | object | SaaS B2B raramente segmenta só por user — segmenta por conta. Lugar canônico para isso. |

**Todos opcionais.** Cliente que passa apenas `id` funciona. Cliente que passa tudo segmenta com profundidade.

### 2.3 O papel do `attributes`

O `attributes` resolve o problema do "não conseguir prever o domínio do cliente". Suporta:

- **String:** `industry: 'agência'`
- **Número:** `employees: 87`
- **Booleano:** `beta_program: true`
- **Array:** `tags: ['vip']`
- **Data ISO 8601:** `last_purchase: '2026-06-12'` (Novidda detecta formato)

**Fronteira clara:** tudo dentro de `attributes` é do cliente, Novidda não interpreta — apenas armazena para comparação. Tudo fora é canônico.

### 2.4 Validação leve

A validação é tolerante. O widget nunca quebra o sistema do cliente.

| Campo | Regra | Falha = |
|---|---|---|
| `id` | string ou número | ignora campo, loga warning |
| `email` | regex simples de e-mail | ignora campo, loga warning |
| `created_at` | ISO 8601 ou timestamp parseável | ignora campo, loga warning |
| `attributes` | objeto plano (1 nível de profundidade) | ignora chaves aninhadas, loga warning |
| Tamanho total do `user` | ≤ 8 KB | trunca, loga warning |

Em modo debug (`?novidda_debug=1`), painel mostra erros visualmente.

### 2.5 Como o cliente integra do lado dele

Trivial em qualquer framework. Exemplo em Laravel/Blade:

```php
<script>
  window.noviddaConfig = {
    token: '{{ config('novidda.token') }}',
    user: @json([
      'id' => auth()->user()->id,
      'email' => auth()->user()->email,
      'name' => auth()->user()->name,
      'plan' => auth()->user()->subscription->plan_name,
      'role' => auth()->user()->role,
      'created_at' => auth()->user()->created_at->toIso8601String(),
      'company' => [
        'id' => auth()->user()->company->id,
        'name' => auth()->user()->company->name,
        'plan' => auth()->user()->company->plan,
      ],
      'attributes' => [
        'industry' => auth()->user()->company->industry,
        'employees' => auth()->user()->company->employees_count,
        'beta_program' => auth()->user()->in_beta,
      ],
    ])
  };
</script>
<script src="https://novidda.com.br/widget.js" async></script>
```

---

## 3. Interface no admin — abas no formulário do changelog

### 3.1 Estrutura geral do formulário

O formulário de criar/editar changelog passa a ter três abas. A aba ativa controla o conteúdo exibido. Os botões "Salvar" e "Publicar" ficam **fora das abas**, sempre visíveis no rodapé, e salvam o estado de todas as abas simultaneamente.

```
┌────────────────────────────────────────────────────────────────┐
│  ◀  Novo changelog                                              │
│                                                                  │
│   ┌─────────────┬──────────┬──────────────┐                    │
│   │ Conteúdo    │  Widget  │ Segmentação  │                    │
│   └─────────────┴──────────┴──────────────┘                    │
│   ═══════════════════════════════                              │
│                                                                  │
│   [conteúdo da aba ativa]                                       │
│                                                                  │
│                                                                  │
├──────────────────────────────────────────────────────────────────┤
│              [Cancelar]   [Salvar rascunho]   [Publicar]        │
└────────────────────────────────────────────────────────────────┘
```

### 3.2 Conteúdo de cada aba

**Aba "Conteúdo":**
Título, descrição (rich text), tipo, status, categorias, mídia (imagens + YouTube), emoji da reação.

**Aba "Widget":**
Configurações específicas de exibição no widget (mostrar comentários, permitir comentários, mostrar reações), botão de ação (CTA), banner contextual (URLs onde aparece).

**Aba "Segmentação"** *(esta especificação)*:
Quem pode ver o changelog. Default é todos.

### 3.3 Indicadores visuais nas abas

Cada aba mostra um indicador discreto quando há configuração ativa, ajudando o autor a saber o estado sem precisar entrar em cada uma:

| Aba | Indicador quando | Visual |
|---|---|---|
| Widget | Banner contextual ativo | Ponto violeta após o nome |
| Segmentação | Regras configuradas | Ponto violeta + contador `(3)` |

```
┌─────────────┬───────────────┬──────────────────────┐
│ Conteúdo    │  Widget  ·    │ Segmentação  · (3)  │
└─────────────┴───────────────┴──────────────────────┘
```

### 3.4 Layout da aba "Segmentação"

```
┌──────────────────────────────────────────────────────────────┐
│  Quem vai ver este changelog                                  │
│  Defina quem entre seus usuários pode ver esta release.       │
│                                                                │
│  ┌──────────────────────────────────────────────────────────┐│
│  │ ●  Todos os usuários                                      ││
│  │    Default. Não aplica filtro.                            ││
│  │                                                            ││
│  │ ◯  Apenas usuários que correspondem a regras              ││
│  └──────────────────────────────────────────────────────────┘│
│                                                                │
│  ──────────────────────────────────────────────────────────  │
│                                                                │
│  Regras  (todas precisam ser verdadeiras)                     │
│                                                                │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  Atributo            Operador        Valor            │   │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────┐  × │   │
│  │  │ plan       ▾ │  │ igual a    ▾ │  │ pro      │    │   │
│  │  └──────────────┘  └──────────────┘  └──────────┘    │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                                │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────┐  × │   │
│  │  │ role       ▾ │  │ está em    ▾ │  │ admin,...│    │   │
│  │  └──────────────┘  └──────────────┘  └──────────┘    │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                                │
│  [ + Adicionar regra ]                                        │
│                                                                │
│  ──────────────────────────────────────────────────────────  │
│                                                                │
│  Alcance estimado                                              │
│                                                                │
│   ┌──────────────────────────────────────────────────┐       │
│   │  340 de 540 usuários ativos (62%)                 │       │
│   │  ████████████████████████░░░░░░░░░░░░░░          │       │
│   │  [ Pré-visualizar quem vê ]                       │       │
│   └──────────────────────────────────────────────────┘       │
│                                                                │
│  ──────────────────────────────────────────────────────────  │
│                                                                │
│  ▸  O que acontece com usuários anônimos?                     │
│     (expansível)                                               │
│                                                                │
└──────────────────────────────────────────────────────────────┘
```

### 3.5 Componente de regra individual

Cada regra é uma linha com três campos + botão remover:

**Campo "Atributo" (dropdown com busca):**
Lista organizada em três grupos:

```
┌────────────────────────────────────────────┐
│  Buscar atributo...                         │
│                                             │
│  Campos do Novidda                          │
│  ├─ plan                                    │
│  ├─ role                                    │
│  ├─ email                                   │
│  ├─ name                                    │
│  ├─ created_at                              │
│  ├─ company.id                              │
│  ├─ company.name                            │
│  └─ company.plan                            │
│                                             │
│  Atributos da sua conta (auto-detectados)  │
│  ├─ attributes.industry      (87% dos users)│
│  ├─ attributes.city          (92%)          │
│  ├─ attributes.employees     (45%)          │
│  ├─ attributes.beta_program  (12%)          │
│  └─ attributes.mrr           (78%)          │
│                                             │
│  + Digitar atributo customizado            │
└────────────────────────────────────────────┘
```

A porcentagem ao lado é a **cobertura** — quantos % dos leitores únicos que vieram nos últimos 30 dias passaram esse atributo. Excelente sinal pro autor saber se a regra vai ter alcance.

**Campo "Operador" (dropdown adaptativo):**
Os operadores disponíveis dependem do tipo detectado do atributo:

| Tipo do atributo | Operadores disponíveis |
|---|---|
| String | igual a, diferente de, contém, começa com, termina com, está em, não está em, existe, não existe |
| Número | igual a, diferente de, maior que, menor que, está em, não está em, existe, não existe |
| Booleano | igual a, existe, não existe |
| Data | igual a, anterior a, posterior a, existe, não existe |
| Array | está em, não está em, existe, não existe |

**Campo "Valor":**
Input adaptativo. Para tipos enumerados (`role`, `plan`), oferece autocomplete com valores detectados nos payloads. Para operadores que aceitam múltiplos valores (`está em`), input com tags separadas por vírgula. Para datas, datepicker.

### 3.6 Alcance estimado em tempo real

A cada mudança nas regras, recalcula o alcance via debounce (300ms). Mostra:

- **Número absoluto:** "340 de 540 usuários ativos"
- **Percentual:** "(62%)"
- **Barra visual:** preenchida proporcionalmente

A base de cálculo é "usuários únicos que carregaram o widget nos últimos 30 dias". Esse universo precisa estar agregado em uma tabela espelho para performance (ver seção 8).

### 3.7 Pré-visualização

Botão "Pré-visualizar quem vê" abre modal com lista paginada dos primeiros leitores que bateriam na regra. Útil para validar regras complexas antes de publicar.

```
┌──────────────────────────────────────────────────────┐
│  Quem vê esta release                          [×]   │
│                                                       │
│  340 usuários atendem aos critérios                  │
│                                                       │
│  ┌────────────────────────────────────────────────┐ │
│  │ ID            email              plan   role    │ │
│  │ user_12345    ana@empresa...     pro    admin   │ │
│  │ user_12346    bruno@empresa...   pro    admin   │ │
│  │ user_12347    carla@empresa...   pro    admin   │ │
│  │ ...                                              │ │
│  └────────────────────────────────────────────────┘ │
│                                                       │
│   ◀  1  2  3  ...  34  ▶                            │
└──────────────────────────────────────────────────────┘
```

### 3.8 Expansível "O que acontece com usuários anônimos?"

Bloco colapsado por padrão, explica:

> Usuários anônimos (que não enviam dados via `window.noviddaConfig.user`) **não veem** changelogs com regras de segmentação ativas. Para que esses usuários vejam o changelog, deixe a opção "Todos os usuários" selecionada.
>
> Atualmente **18% dos seus leitores** são anônimos.

---

## 4. Modelo de dados

### 4.1 Migrations

```php
// 2026_07_15_000001_add_segment_enabled_to_changelogs.php
Schema::table('changelogs', function (Blueprint $table) {
    $table->boolean('segment_enabled')->default(false)->after('status');
    $table->index('segment_enabled');
});

// 2026_07_15_000002_create_changelog_segment_rules.php
Schema::create('changelog_segment_rules', function (Blueprint $table) {
    $table->id();
    $table->foreignId('changelog_id')->constrained()->cascadeOnDelete();
    $table->string('attribute', 100);
    $table->enum('operator', [
        'equals', 'not_equals',
        'contains', 'starts_with', 'ends_with',
        'greater_than', 'less_than',
        'before', 'after',
        'in', 'not_in',
        'exists', 'not_exists'
    ]);
    $table->json('value')->nullable();
    $table->unsignedSmallInteger('position')->default(0);
    $table->timestamps();

    $table->index('changelog_id');
});

// 2026_07_15_000003_create_user_attribute_index.php
// Tabela espelho para auto-descoberta e estimativa de alcance.
// Atualizada via job assíncrono a cada chamada do widget.
Schema::create('user_attribute_index', function (Blueprint $table) {
    $table->id();
    $table->foreignId('account_id')->constrained()->cascadeOnDelete();
    $table->string('reader_id', 64);
    $table->json('attributes_snapshot'); // user completo (sem PII sensível) achatado
    $table->timestamp('last_seen_at');
    $table->timestamps();

    $table->unique(['account_id', 'reader_id']);
    $table->index(['account_id', 'last_seen_at']);
});

// 2026_07_15_000004_create_attribute_discovery_cache.php
// Agregação leve para popular o dropdown de atributos descobertos.
// Recalculada por job agendado (a cada hora).
Schema::create('attribute_discovery_cache', function (Blueprint $table) {
    $table->id();
    $table->foreignId('account_id')->constrained()->cascadeOnDelete();
    $table->string('attribute_path', 100); // ex: 'attributes.industry'
    $table->string('detected_type', 20);   // string, number, boolean, date, array
    $table->unsignedInteger('coverage_count'); // quantos users têm esse atributo
    $table->json('sample_values')->nullable(); // até 20 valores únicos para autocomplete
    $table->timestamp('updated_at');

    $table->unique(['account_id', 'attribute_path']);
});
```

### 4.2 Considerações sobre privacidade do índice

A tabela `user_attribute_index` armazena snapshots do `user` que chega pelo widget. Para evitar acúmulo de PII desnecessário:

- **Excluir do snapshot:** `email`, `name`, `id` (PII direta) — manter apenas em colunas próprias indexadas, se necessário.
- **Reter por janela:** registros com `last_seen_at` < 30 dias são purgados via job diário.
- **Hash do reader_id:** se o cliente Novidda preferir, fornecer modo "anônimo" onde `reader_id` é hash, perdendo a capacidade de targeting individual mas ganhando privacidade.

---

## 5. Matching no servidor

### 5.1 Service de matching

```php
// app/Services/SegmentMatcher.php
namespace App\Services;

use App\Models\Changelog;
use Carbon\Carbon;

class SegmentMatcher
{
    public function matches(Changelog $changelog, ?array $user): bool
    {
        if (!$changelog->segment_enabled) {
            return true; // sem segmentação = todos veem
        }

        if (empty($user)) {
            return false; // anônimo + regras = não vê
        }

        // Lógica AND entre todas as regras
        foreach ($changelog->segmentRules as $rule) {
            $userValue = data_get($user, $rule->attribute);
            if (!$this->evaluate($userValue, $rule->operator, $rule->value)) {
                return false;
            }
        }

        return true;
    }

    private function evaluate($userValue, string $operator, $ruleValue): bool
    {
        return match($operator) {
            'equals'      => $this->loose($userValue) === $this->loose($ruleValue),
            'not_equals'  => $this->loose($userValue) !== $this->loose($ruleValue),
            'contains'    => is_string($userValue) && is_string($ruleValue)
                              && str_contains(mb_strtolower($userValue), mb_strtolower($ruleValue)),
            'starts_with' => is_string($userValue) && is_string($ruleValue)
                              && str_starts_with(mb_strtolower($userValue), mb_strtolower($ruleValue)),
            'ends_with'   => is_string($userValue) && is_string($ruleValue)
                              && str_ends_with(mb_strtolower($userValue), mb_strtolower($ruleValue)),
            'greater_than'=> is_numeric($userValue) && is_numeric($ruleValue)
                              && (float)$userValue > (float)$ruleValue,
            'less_than'   => is_numeric($userValue) && is_numeric($ruleValue)
                              && (float)$userValue < (float)$ruleValue,
            'before'      => $userValue && $ruleValue
                              && Carbon::parse($userValue)->lt(Carbon::parse($ruleValue)),
            'after'       => $userValue && $ruleValue
                              && Carbon::parse($userValue)->gt(Carbon::parse($ruleValue)),
            'in'          => is_array($ruleValue) && in_array($userValue, $ruleValue),
            'not_in'      => is_array($ruleValue) && !in_array($userValue, $ruleValue),
            'exists'      => $userValue !== null,
            'not_exists'  => $userValue === null,
            default       => false,
        };
    }

    private function loose($value)
    {
        // Normaliza booleanos string ↔ bool ("true" === true)
        if (is_string($value)) {
            $lower = strtolower($value);
            if ($lower === 'true') return true;
            if ($lower === 'false') return false;
        }
        return $value;
    }
}
```

### 5.2 Aplicação no controller

```php
// app/Http/Controllers/Widget/FeedController.php
public function index(Request $request, string $token)
{
    $account = Account::where('widget_token', $token)->firstOrFail();

    $user = $this->parseUserContext($request);

    // Indexa o usuário para auto-descoberta (job assíncrono)
    if ($user && !empty($user['id'] ?? null)) {
        IndexUserAttributesJob::dispatch($account->id, $user);
    }

    $cacheKey = $this->buildCacheKey($account->widget_token, $user);

    $feed = Cache::remember($cacheKey, 300, function () use ($account, $user) {
        $matcher = new SegmentMatcher();

        return $account->changelogs()
            ->where('status', 'published')
            ->with('segmentRules')
            ->get()
            ->filter(fn($c) => $matcher->matches($c, $user))
            ->values()
            ->map(fn($c) => $this->serialize($c));
    });

    return response()->json(['feed' => $feed])
        ->header('Cache-Control', 'private, max-age=60');
}

private function parseUserContext(Request $request): ?array
{
    $payload = $request->input('user');

    if (!is_array($payload)) return null;

    // Sanitização: limita tamanho e profundidade
    $serialized = json_encode($payload);
    if (strlen($serialized) > 8192) return null;

    // Achata attributes para 1 nível
    if (isset($payload['attributes']) && is_array($payload['attributes'])) {
        $payload['attributes'] = array_filter(
            $payload['attributes'],
            fn($v) => !is_array($v) || $this->isFlatArray($v)
        );
    }

    return $payload;
}
```

### 5.3 Estratégia de cache

O feed varia por usuário, mas cachear individualmente é inviável. A solução é **cachear por "shape" relevante**:

```php
private function buildCacheKey(string $token, ?array $user): string
{
    if (empty($user)) {
        return "novidda:feed:{$token}:anon";
    }

    // Pega apenas os atributos que aparecem em alguma regra ativa da conta.
    // Dois usuários com mesmos valores nesses atributos compartilham cache.
    $relevantAttrs = $this->getRelevantAttributePaths($token);

    $shape = [];
    foreach ($relevantAttrs as $path) {
        $shape[$path] = data_get($user, $path);
    }

    return "novidda:feed:{$token}:" . md5(json_encode($shape));
}

private function getRelevantAttributePaths(string $token): array
{
    return Cache::remember(
        "novidda:relevant_attrs:{$token}",
        3600,
        fn() => ChangelogSegmentRule::query()
            ->whereHas('changelog', fn($q) =>
                $q->where('account_id', $accountId)
                  ->where('status', 'published')
                  ->where('segment_enabled', true))
            ->distinct()
            ->pluck('attribute')
            ->toArray()
    );
}
```

### 5.4 Invalidação de cache

Como o driver `file`/`database` do Laravel não suporta tags nativas, mantemos uma **chave índice** com a lista de chaves geradas por conta:

```php
// app/Services/FeedCacheManager.php
class FeedCacheManager
{
    public static function invalidate(string $token): void
    {
        // Invalida a lista de atributos relevantes (pode ter mudado)
        Cache::forget("novidda:relevant_attrs:{$token}");

        // Invalida todas as variações de feed cacheadas
        $keys = Cache::get("novidda:feed_keys:{$token}", []);
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        Cache::forget("novidda:feed_keys:{$token}");
    }

    public static function trackKey(string $token, string $key): void
    {
        $keys = Cache::get("novidda:feed_keys:{$token}", []);
        if (!in_array($key, $keys)) {
            $keys[] = $key;
            Cache::put("novidda:feed_keys:{$token}", $keys, 86400);
        }
    }
}
```

Disparada por observers do Eloquent:

```php
// app/Observers/ChangelogObserver.php
public function saved(Changelog $changelog)
{
    FeedCacheManager::invalidate($changelog->account->widget_token);
}

// app/Observers/SegmentRuleObserver.php
public function saved(ChangelogSegmentRule $rule)
{
    FeedCacheManager::invalidate($rule->changelog->account->widget_token);
}

public function deleted(ChangelogSegmentRule $rule)
{
    FeedCacheManager::invalidate($rule->changelog->account->widget_token);
}
```

> **Nota Fase 3:** quando migrar para Redis, substituir essa lógica por `Cache::tags(['feed', $token])->flush()`, eliminando a chave índice.

---

## 6. Auto-descoberta de atributos

### 6.1 Por que existe

Sem auto-descoberta, o cliente Novidda precisaria digitar manualmente os nomes dos atributos que ele mesmo passa via `attributes`. Erros de digitação ("intustry" em vez de "industry") viram bugs silenciosos. A auto-descoberta resolve isso oferecendo no dropdown apenas os atributos que **realmente estão chegando** dos usuários.

### 6.2 Como funciona

O fluxo:

```
1. Widget chama /feed com user payload
       │
       ▼
2. Controller despacha IndexUserAttributesJob (fila)
       │
       ▼
3. Job grava/atualiza linha em user_attribute_index
   (snapshot do user, sem PII direta)
       │
       ▼
4. Job agendado horário roda RebuildAttributeDiscoveryJob
   que agrega user_attribute_index em attribute_discovery_cache
       │
       ▼
5. Endpoint do admin /api/admin/attributes/discovery
   serve a lista pronta pro dropdown
```

### 6.3 Job de indexação

```php
// app/Jobs/IndexUserAttributesJob.php
class IndexUserAttributesJob implements ShouldQueue
{
    public function __construct(
        public int $accountId,
        public array $user
    ) {}

    public function handle(): void
    {
        // Remove PII direta antes de armazenar
        $snapshot = $this->user;
        unset($snapshot['email'], $snapshot['name']);

        UserAttributeIndex::updateOrCreate(
            [
                'account_id' => $this->accountId,
                'reader_id' => (string) ($this->user['id'] ?? 'anon'),
            ],
            [
                'attributes_snapshot' => $snapshot,
                'last_seen_at' => now(),
            ]
        );
    }
}
```

### 6.4 Job de agregação horária

```php
// app/Jobs/RebuildAttributeDiscoveryJob.php
class RebuildAttributeDiscoveryJob implements ShouldQueue
{
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

        if ($records->isEmpty()) return;

        $paths = [];
        foreach ($records as $record) {
            $flat = $this->flatten($record->attributes_snapshot);
            foreach ($flat as $path => $value) {
                if (!isset($paths[$path])) {
                    $paths[$path] = [
                        'count' => 0,
                        'type' => $this->detectType($value),
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
                    'detected_type' => $meta['type'],
                    'coverage_count' => $meta['count'],
                    'sample_values' => array_slice($meta['samples'], 0, 20),
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

    private function detectType($value): string
    {
        if (is_bool($value)) return 'boolean';
        if (is_numeric($value)) return 'number';
        if (is_array($value)) return 'array';
        if (is_string($value) && $this->isIsoDate($value)) return 'date';
        return 'string';
    }
}
```

Agendado no `app/Console/Kernel.php`:

```php
$schedule->job(new RebuildAttributeDiscoveryJob)->hourly();
```

### 6.5 Endpoint admin para o dropdown

```php
// GET /api/admin/attributes/discovery
public function discovery()
{
    $account = auth()->user()->account;

    $total = UserAttributeIndex::where('account_id', $account->id)
        ->where('last_seen_at', '>', now()->subDays(30))
        ->count();

    $discovered = AttributeDiscoveryCache::where('account_id', $account->id)
        ->orderBy('coverage_count', 'desc')
        ->get()
        ->map(fn($a) => [
            'path' => $a->attribute_path,
            'type' => $a->detected_type,
            'coverage_pct' => $total > 0 ? round($a->coverage_count / $total * 100) : 0,
            'sample_values' => $a->sample_values,
        ]);

    return response()->json([
        'canonical' => $this->canonicalAttributes(),
        'discovered' => $discovered,
        'total_users_30d' => $total,
    ]);
}

private function canonicalAttributes(): array
{
    return [
        ['path' => 'id', 'type' => 'string'],
        ['path' => 'email', 'type' => 'string'],
        ['path' => 'name', 'type' => 'string'],
        ['path' => 'plan', 'type' => 'string'],
        ['path' => 'role', 'type' => 'string'],
        ['path' => 'created_at', 'type' => 'date'],
        ['path' => 'company.id', 'type' => 'string'],
        ['path' => 'company.name', 'type' => 'string'],
        ['path' => 'company.plan', 'type' => 'string'],
    ];
}
```

---

## 7. Estimativa de alcance

### 7.1 Endpoint dedicado

Recalcula sempre que o autor mexe nas regras (com debounce).

```
POST /api/admin/changelogs/{id}/estimate-reach
Body: { "rules": [...] }
```

```php
public function estimateReach(Request $request, Changelog $changelog)
{
    $rules = $request->input('rules', []);

    $totalActive = UserAttributeIndex::where('account_id', $changelog->account_id)
        ->where('last_seen_at', '>', now()->subDays(30))
        ->count();

    if (empty($rules)) {
        return response()->json([
            'matched' => $totalActive,
            'total' => $totalActive,
            'percentage' => 100,
        ]);
    }

    // Para volumes pequenos (< 50k users), filtragem em PHP funciona.
    // Para volumes maiores, considerar otimização via JSON_EXTRACT no MySQL 8
    // (limitação documentada na seção 9).
    $matcher = new SegmentMatcher();
    $virtualChangelog = new Changelog([
        'segment_enabled' => true,
    ]);
    $virtualChangelog->setRelation('segmentRules', collect($rules)->map(
        fn($r) => new ChangelogSegmentRule($r)
    ));

    $matched = UserAttributeIndex::where('account_id', $changelog->account_id)
        ->where('last_seen_at', '>', now()->subDays(30))
        ->get()
        ->filter(fn($u) => $matcher->matches($virtualChangelog, $u->attributes_snapshot))
        ->count();

    return response()->json([
        'matched' => $matched,
        'total' => $totalActive,
        'percentage' => $totalActive > 0 ? round($matched / $totalActive * 100) : 0,
    ]);
}
```

### 7.2 Debounce no frontend

A cada mudança nas regras (digitação, seleção, remoção), aguarda 300ms de inatividade antes de chamar:

```javascript
let debounceTimer;
function onRulesChange() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(refreshEstimate, 300);
}

async function refreshEstimate() {
  const rules = collectRulesFromForm();
  showLoadingState();
  const response = await fetch(`/api/admin/changelogs/${id}/estimate-reach`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ rules }),
  });
  const data = await response.json();
  renderReachWidget(data);
}
```

### 7.3 Pré-visualização da lista

Mesma lógica, mas retorna a coleção paginada em vez de só a contagem. Endpoint:

```
POST /api/admin/changelogs/{id}/preview-audience?page=1
Body: { "rules": [...] }
```

---

## 8. Endpoints completos da API

### 8.1 API pública do widget (atualizada)

| Endpoint | Mudança |
|---|---|
| `POST /api/v1/widget/{token}/feed` | Agora aceita `user` no body. Aplica matching. Retorna feed filtrado. |
| Demais endpoints | Sem mudança no contrato, mas internamente registram o `user` para auto-descoberta. |

### 8.2 API do admin (novos endpoints)

| Endpoint | Função |
|---|---|
| `GET /api/admin/attributes/discovery` | Lista atributos canônicos + descobertos para popular dropdown. |
| `POST /api/admin/changelogs/{id}/estimate-reach` | Estimativa em tempo real do alcance de um conjunto de regras. |
| `POST /api/admin/changelogs/{id}/preview-audience` | Lista paginada de usuários que bateriam nas regras. |
| `GET /api/admin/changelogs/{id}/segment-rules` | Carrega regras existentes do changelog. |
| `PUT /api/admin/changelogs/{id}/segment-rules` | Substitui o conjunto de regras (idempotente). |

---

## 9. Limitações e trade-offs documentados

### 9.1 Volume de usuários ativos

A estimativa de alcance roda em PHP filtrando todos os snapshots. Funciona bem até ~50k usuários ativos por conta. Acima disso:

- **Curto prazo:** paginar a estimativa, mostrar amostra ("baseado em amostra de 10k usuários").
- **Médio prazo (Fase 3):** migrar para MySQL 8 com `JSON_EXTRACT` em queries SQL, ou indexar atributos canônicos em colunas dedicadas.
- **Longo prazo:** considerar adoção de banco analítico (ClickHouse) para targeting de larga escala.

### 9.2 PII e LGPD

Os snapshots em `user_attribute_index` armazenam atributos do usuário do cliente final — não do cliente Novidda diretamente. Mesmo assim, há implicações de LGPD:

- O cliente Novidda é controlador desses dados; Novidda é operador.
- Documentar isso no termo de uso.
- Fornecer endpoint para o cliente Novidda solicitar purge de um `reader_id` específico (direito ao esquecimento).
- Considerar configuração "modo privacy" onde `email` e `name` são hasheados no índice (perdendo capacidade de mostrar no preview de audiência).

### 9.3 Confiabilidade dos atributos

Dados do `noviddaConfig.user` vêm do browser e são manipuláveis via DevTools. Para o contexto de changelog, isso é aceitável — o pior caso é alguém ver um anúncio que não deveria. Para um futuro com features sensíveis (ex: gating de conteúdo pago), considerar HMAC verification (ver Fase 3 abaixo).

### 9.4 Cache hit rate

A estratégia de "cache por shape" depende dos atributos usados em regras serem **poucos e de baixa cardinalidade**. Se um cliente cria regras usando `attributes.user_id`, cada usuário gera uma chave única — explosão de cache. Mitigação:

- Logar atributos com cardinalidade > 100 valores únicos e avisar o cliente Novidda na UI.
- Limitar número de regras por changelog (ex: 10).
- Limitar tamanho do `attributes` aceito (já no contrato — 8 KB).

### 9.5 Lógica AND apenas (no MVP)

Toda a configuração é AND entre regras. Para casos como "plano pro **OU** enterprise", o autor precisa usar `está em` (`plan in [pro, enterprise]`). Isso cobre 90% dos casos. OR aninhado fica para Fase 3.

---

## 10. Roadmap de entrega

### Sprint 1 — Modelo e API base
- Migrations das tabelas (`segment_enabled`, `changelog_segment_rules`, `user_attribute_index`, `attribute_discovery_cache`).
- Models, relacionamentos, factories.
- `SegmentMatcher` service com todos os operadores.
- Atualização do endpoint `/feed` para aceitar `user` e aplicar matching.
- Refatoração do cache para suportar variação por shape.
- Testes de matching (todos os operadores, dot-notation, casos de borda).

### Sprint 2 — Auto-descoberta
- `IndexUserAttributesJob` despachado pelo endpoint `/feed`.
- `RebuildAttributeDiscoveryJob` agendado horário.
- Endpoint `/api/admin/attributes/discovery`.
- Endpoint de estimativa de alcance.
- Endpoint de pré-visualização paginada.
- Job de purge de dados > 30 dias.

### Sprint 3 — UI do admin
- Refatoração do form de changelog para layout em abas.
- Aba "Segmentação" com toggle Todos / Segmentado.
- Componente de regra (dropdown atributo + operador adaptativo + input de valor).
- Integração com endpoint de descoberta (dropdown agrupado).
- Estimativa de alcance em tempo real com debounce.
- Indicadores nas abas (ponto violeta + contador).
- Modal de pré-visualização.
- Expansível "O que acontece com anônimos?".

### Sprint 4 — Refinamento e launch
- Documentação para clientes (como configurar `noviddaConfig.user`).
- Exemplos de código para Laravel, Rails, Next.js, plain PHP.
- Modo debug (`?novidda_debug=1`).
- Testes end-to-end em SaaS de exemplo.
- Página de "como funciona a segmentação" no help center.

**Estimativa total:** 4 sprints (~2 meses com 1 dev + meia mão de QA/design).

---

## 11. Interação com outras features do Novidda

### 11.1 Banner contextual

Segmentação é **ortogonal** ao banner contextual. Um changelog pode ter:
- Regras de URL (banner aparece em `/relatorios`)
- Regras de segmentação (só pra `plan=pro`)

Resultado combinado: banner aparece em `/relatorios` **e** apenas pra usuários `pro`. As duas features são aplicadas em sequência no widget — primeiro filtra feed por segmentação, depois aplica matching de URL para banners.

### 11.2 Reações e comentários

Reações e comentários são vinculadas ao `reader_id` (ou ao `user.id` se disponível). Não há mudança no fluxo, mas:
- Comentários de um usuário que **agora** não atende ao segmento (ex: cliente desabilitou plano) **continuam visíveis** no histórico — segmentação afeta visibilidade do changelog inteiro, não do histórico.
- Quando o changelog é segmentado, comentários só aparecem para usuários que atendem ao segmento.

### 11.3 Analytics

Métricas precisam de novo contexto:
- "Visto por 340 de 540 usuários elegíveis (62%)" — em vez de só "340 views". Sem isso, taxa parece baixa quando audiência é restrita.
- Comparativo justo: `views / elegíveis`, não `views / total da base`.
- Funil considera o universo segmentado.

### 11.4 Página pública de changelog (Fase 3)

A página pública (`seusistema.com.br/sua-conta`) **não tem contexto de usuário** — é indexável e acessada anonimamente. Changelogs com segmentação ativa simplesmente não aparecem na página pública, ou aparecem genericamente (configurável). Decisão a tomar na implementação da página.

---

## 12. Próximos passos imediatos

1. Validar a divisão de abas (`Conteúdo | Widget | Segmentação`) com mockup neumórfico real, garantindo legibilidade dos indicadores.
2. Decidir política de retenção de `user_attribute_index` (30 dias é razoável; ajustar conforme escala).
3. Confirmar que a auto-descoberta horária atende o produto — se clientes esperarem ver atributos novos imediatamente, considerar agregação a cada 15 minutos.
4. Definir comportamento padrão do segmento na página pública (Fase 3) antes de implementar.
5. Iniciar Sprint 1.
