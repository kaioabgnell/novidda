# Novidda — Banner Contextual

> Especificação completa de implementação — MVP e Fase 2
> Estilos suportados: **toast no canto, barra superior, barra inferior**
> Spotlight com recorte de elemento fica para a Fase 3 (fora deste documento)

---

## 1. Visão geral

### 1.1 O que é

O Banner Contextual é uma extensão do widget Novidda que faz um anúncio aparecer dentro da página do sistema do cliente, em **URLs específicas** configuradas pelo usuário Novidda. Diferente do sino (que é um canal passivo onde o usuário precisa abrir pra ver), o banner é um canal **ativo** que se manifesta no contexto certo.

### 1.2 Posicionamento estratégico

O banner contextual transforma o changelog de "documentação do que mudou" em **ferramenta de adoção de produto**. Move o Novidda da categoria *comunicação* (Headway, Changecrab) para *PLG light* (Appcues, Pendo) por uma fração do preço.

### 1.3 Princípios de design da feature

- **Zero acoplamento com o DOM do cliente.** O Novidda não precisa saber nada sobre a estrutura HTML do sistema integrado.
- **Configuração mínima.** O cliente Novidda informa apenas URL e posição. Sem seletores CSS, sem inspecionar HTML.
- **Performance preservada.** O módulo do banner é carregado lazy, sem impactar o carregamento da página do cliente.
- **Funciona universalmente.** Em qualquer SaaS — React, Vue, Angular, Blade, jQuery, Wordpress. Tanto faz.

### 1.4 O que está dentro e o que está fora do escopo

**Dentro do escopo (este documento):**
- Estilos: **toast no canto** (4 variações de posição), **barra superior**, **barra inferior**.
- Sobreposições puras (`position: fixed`) sem prender a nenhum elemento do cliente.
- Matching por URL com modos `exact`, `contains`, `starts_with`, `regex`.
- Suporte a SPAs (detecção de mudança de rota sem reload).
- Frequência e expiração.
- Analytics de impressão/dispensa/clique.
- Admin no painel Novidda para configurar.

**Fora do escopo (Fase 3):**
- Spotlight com recorte de elemento e seta apontando.
- Tours guiados multi-passo.
- Calcular posição dinâmica baseada em elemento.

---

## 2. Benefícios para cada lado

### 2.1 Para o usuário final do SaaS do cliente

- Descobre a feature **no contexto certo**, quando ela faz sentido para a tarefa que está executando.
- Não precisa decorar nem revisitar o changelog para lembrar do que existe.
- Reduz a frustração do *"essa funcionalidade existia e eu não sabia"*.

### 2.2 Para o cliente Novidda (a empresa SaaS)

- **Taxa de adoção real sobe muito** — anúncio no contexto converte significativamente mais que anúncio descontextualizado.
- Reduz tickets de suporte tipo "como faço X?" — o sistema mostra.
- Métrica direta de ROI do changelog: *"X% dos usuários que viram o banner usaram a feature em 7 dias"*.
- Justifica investimento em features que normalmente passariam batido.

### 2.3 Para o Novidda como produto

- É a ponte entre comunicação (commodity) e adoção de produto (categoria premium).
- Diferencia da concorrência nacional, que oferece apenas widget de sino.
- Justifica plano pago de tier superior.

---

## 3. Estilos suportados

### 3.1 Toast no canto

Card pequeno (~320px de largura) com slide-up suave, posicionado em uma das quatro extremidades.

**Posições suportadas:**
- Canto inferior direito (padrão recomendado)
- Canto inferior esquerdo
- Canto superior direito
- Canto superior esquerdo

**Estrutura visual:**
- Ícone pequeno (categoria/tipo do changelog) à esquerda
- Título (1 linha) + descrição curta (até 2 linhas)
- CTA (botão) opcional
- Botão fechar (X) no canto superior direito do toast

**Quando usar:** anúncios pontuais de feature. É o estilo padrão recomendado por ser discreto.

### 3.2 Barra superior

Faixa horizontal no topo da viewport, ocupando toda a largura, com altura baixa (40–50px).

**Estrutura visual:**
- Ícone + texto curto (1 linha) centralizados ou à esquerda
- CTA opcional à direita
- Botão fechar à extrema direita

**Quando usar:** comunicados institucionais (manutenção programada, mudança de planos, aviso amplo).

### 3.3 Barra inferior

Mesma ideia da superior, mas ancorada no rodapé da viewport. Menos intrusiva, recomendada para anúncios "ambientes" de longa duração.

### 3.4 Coexistência com o sino do widget

O sino continua sendo o **arquivo permanente** do changelog. O banner é o **empurrão pontual** para releases específicas.

- A mesma release pode aparecer em ambos os canais.
- Se o usuário dispensa o banner, ele ainda encontra a release no sino.
- Clicar em "Ver detalhes" no banner pode abrir o painel do widget já expandido naquela release.
- Posicionamento: o banner toast respeita o espaço do sino (offset automático para não sobrepor).

---

## 4. Configuração pelo cliente Novidda (admin)

### 4.1 Onde fica no painel

Dentro do formulário de criação/edição de changelog, adicionar uma seção colapsada chamada **"Banner contextual"**. Por padrão, fica desligada.

### 4.2 Campos do formulário

| Campo | Tipo | Descrição |
|---|---|---|
| Ativar banner contextual | Toggle | Liga/desliga a feature para este changelog. |
| Estilo | Select | `toast` · `barra superior` · `barra inferior`. |
| Posição (se toast) | Select | `inferior direito` · `inferior esquerdo` · `superior direito` · `superior esquerdo`. |
| Regras de URL | Lista | Uma ou mais regras de inclusão (OR lógico). |
| URLs de exclusão | Lista | URLs onde **não** deve aparecer (subrotas, exceções). |
| Texto customizado | Texto | Opcional. Sobrescreve o título da release se preenchido (versão curta para o banner). |
| Texto do botão (CTA) | Texto | Opcional. Reaproveita o CTA do changelog se vazio. |
| URL do CTA | URL | Opcional. |
| Abrir em nova aba | Checkbox | Padrão: desligado. |
| Frequência | Select | `uma vez por usuário` (padrão) · `até clicar no CTA` · `N vezes`. |
| N (se frequência limitada) | Número | Quantas vezes mostrar. |
| Auto-fechar após (segundos) | Número | Opcional. Padrão: sem auto-fechar. |
| Expirar em | Data | Opcional. Após essa data, banner para de aparecer. |
| Pré-visualizar | Botão | Renderiza o banner com os dados atuais no próprio admin. |

### 4.3 Configuração das regras de URL

Cada regra tem dois campos:

- **Modo de match:**
  - `exact` — a URL precisa ser exatamente igual.
  - `contains` — a URL contém a string (padrão recomendado).
  - `starts_with` — a URL começa com a string.
  - `regex` — expressão regular (modo avançado, com validação).
- **Padrão:** a string ou regex a ser comparada contra `window.location.pathname` (ou `href` completo se contiver protocolo).

**Exemplos de configuração:**

| Cenário | Modo | Padrão |
|---|---|---|
| Mostrar em qualquer rota de relatórios | `starts_with` | `/relatorios` |
| Mostrar só na home | `exact` | `/dashboard` |
| Mostrar em qualquer tela de configurações | `contains` | `/config` |
| Mostrar em todas as rotas exceto admin | `regex` | `^/(?!admin).*` |

### 4.4 Exemplo prático de fluxo

> O cliente "Operand" lançou um novo botão de exportar para Excel na tela de relatórios. No formulário do changelog, ele liga o banner contextual com:
> - Estilo: toast inferior direito
> - Regra de URL: `contains` `/relatorios`
> - Texto: "Novo: Exporte seus relatórios direto para Excel"
> - CTA: "Testar agora" → `/relatorios?demo=export`
> - Frequência: uma vez por usuário
> - Expirar em: 30 dias após a publicação
>
> Resultado: cada usuário que entrar em qualquer URL contendo `/relatorios` nos próximos 30 dias verá o banner uma única vez.

### 4.5 Preview

O botão "Pré-visualizar" abre uma janela modal no admin renderizando o banner exatamente como ele apareceria — com o estilo escolhido, posição, texto, CTA e cores do tema da conta. Permite ajustes antes de salvar.

---

## 5. Arquitetura técnica

### 5.1 Visão geral do fluxo

```
1. widget.js (sino) já está carregado na página do cliente
       │
       ▼
2. Após DOMContentLoaded, módulo contextual é carregado lazy
       │
       ▼
3. GET /api/v1/widget/{token}/contextual
       │   Retorna lista enxuta de banners ativos (1-5 items)
       │   { banners: [{ id, rules, exclusions, style, position, copy, cta, frequency, expires_at }] }
       │
       ▼
4. Matcher roda no client-side contra window.location.href
       │
       ├─ Nenhum match → fica em standby, escuta mudanças de rota
       │
       └─ Match encontrado
              │
              ▼
       Verifica em localStorage se reader_id já viu/dispensou
              │
              ├─ Já viu (regra de frequência satisfeita) → não mostra
              │
              └─ Não viu → renderiza banner no Shadow DOM
                    │
                    ▼
              POST /api/v1/widget/{token}/contextual/event (evento: shown)
                    │
                    ▼
              Listener de SPA ativo para próximas mudanças de rota
```

### 5.2 Por que o módulo é separado do bundle do sino

O `widget.js` principal (loader do sino) precisa ser mínimo (~2–5 KB) por causa da premissa de performance. O módulo contextual adiciona ~5–8 KB extras, então:

- O loader inicial **não inclui** o código do banner.
- O loader detecta se há banners ativos para o token (info inclusa numa flag no `unread-count` ou no próprio loader cacheado).
- Se houver, carrega `widget-contextual.js` de forma lazy (mesma origem, mesmas otimizações HTTP).
- Se não houver banners ativos para a conta, o módulo nunca é carregado.

### 5.3 Detecção de mudança de rota em SPAs

Sistemas SPA (React, Vue, Angular) trocam a URL via `history.pushState` sem disparar `popstate` nem reload. O matcher precisa interceptar:

- `window.addEventListener('popstate', ...)` — back/forward do navegador.
- Monkey-patch de `history.pushState` e `history.replaceState` — para capturar navegações programáticas.
- Fallback opcional: `MutationObserver` na `<title>` ou polling leve da URL (apenas se o monkey-patch falhar).

A cada mudança detectada, o matcher re-roda contra a nova URL.

### 5.4 Isolamento via Shadow DOM

Todo o banner é renderizado dentro de um Shadow DOM em um elemento root injetado no `<body>` do cliente:

```
<body>
  ...conteúdo do cliente...
  <div id="novidda-contextual-root">
    #shadow-root
      <style>/* estilos isolados */</style>
      <div class="novidda-banner novidda-toast novidda-bottom-right">
        ...
      </div>
  </div>
</body>
```

Vantagens:
- CSS do cliente não afeta o banner.
- CSS do banner não vaza para o cliente.
- O `z-index` interno do shadow é alto e configurável.
- Mesmo padrão já usado pelo sino — reaproveita a infra.

### 5.5 Persistência local da frequência

Chave em `localStorage`:

```
novidda:ctx:{account_token}:{banner_id} = {
  shown_count: 3,
  last_shown_at: "2026-06-26T14:33:00Z",
  dismissed: false,
  clicked: false
}
```

A regra de frequência é avaliada no client antes da renderização. O servidor mantém um espelho via `widget_events` (para analytics), mas a fonte de verdade para "não mostrar de novo" é local — performático e funciona offline.

**Fallback:** se `localStorage` estiver desabilitado/limpo, o servidor pode responder com a flag `seen: true` baseada nos eventos registrados.

### 5.6 Performance — números-alvo

- Carga inicial do `widget.js` (sino): **≤ 5 KB** minificado + gzip.
- Carga do módulo contextual: **≤ 8 KB** minificado + gzip, carregado lazy.
- Latência do endpoint `/contextual`: **≤ 100ms** (cacheado pelo cache nativo do Laravel).
- Tempo entre DOMContentLoaded e renderização do banner (quando aplicável): **≤ 200ms**.
- Impacto em LCP/CLS do site do cliente: **zero** (renderização não bloqueia, posição fixa não causa shift).

---

## 6. Modelo de dados

Migrations a serem adicionadas (MySQL, padrão Laravel).

### 6.1 `changelog_contextual_banners`

```php
Schema::create('changelog_contextual_banners', function (Blueprint $table) {
    $table->id();
    $table->foreignId('changelog_id')->unique()->constrained()->cascadeOnDelete();
    $table->boolean('enabled')->default(false);
    $table->enum('style', ['toast', 'top_bar', 'bottom_bar'])->default('toast');
    $table->enum('position', [
        'bottom_right', 'bottom_left', 'top_right', 'top_left'
    ])->default('bottom_right'); // só aplicável quando style = toast
    $table->enum('frequency', [
        'once_per_user', 'until_clicked', 'times_capped'
    ])->default('once_per_user');
    $table->unsignedSmallInteger('frequency_cap')->nullable(); // se frequency = times_capped
    $table->unsignedSmallInteger('auto_dismiss_seconds')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->string('custom_copy', 500)->nullable(); // texto curto opcional
    $table->string('cta_text', 80)->nullable();
    $table->string('cta_url', 500)->nullable();
    $table->boolean('cta_new_tab')->default(false);
    $table->timestamps();

    $table->index(['enabled', 'expires_at']);
});
```

### 6.2 `changelog_contextual_rules`

```php
Schema::create('changelog_contextual_rules', function (Blueprint $table) {
    $table->id();
    $table->foreignId('banner_id')
        ->constrained('changelog_contextual_banners')
        ->cascadeOnDelete();
    $table->enum('type', ['include', 'exclude']);
    $table->enum('match_mode', ['exact', 'contains', 'starts_with', 'regex']);
    $table->string('pattern', 500);
    $table->timestamps();

    $table->index(['banner_id', 'type']);
});
```

### 6.3 Eventos

Reutiliza a tabela `widget_events` já planejada, adicionando os tipos:

- `contextual_shown`
- `contextual_dismissed`
- `contextual_clicked`

Cada evento guarda `banner_id` (em uma coluna `metadata` JSON) além do `changelog_id` e `reader_id`. Se o volume crescer muito, migrar para tabela dedicada `contextual_impressions` na Fase 3.

### 6.4 Cache invalidation

Sempre que um banner é criado, editado, publicado ou expira, invalidar a chave de cache:

```
novidda:contextual:{account_token}
```

Usar tags do cache do Laravel se o driver suportar (file/database driver não suporta tags — alternativa: chave única por account, sem agrupamento).

---

## 7. API pública do widget

### 7.1 Listar banners ativos

```
GET /api/v1/widget/{token}/contextual
```

**Resposta cacheada (Cache::remember 5 minutos):**

```json
{
  "banners": [
    {
      "id": 42,
      "changelog_id": 128,
      "style": "toast",
      "position": "bottom_right",
      "frequency": "once_per_user",
      "frequency_cap": null,
      "auto_dismiss_seconds": null,
      "expires_at": "2026-07-26T23:59:59Z",
      "copy": {
        "title": "Novo: exportar para Excel",
        "description": "Relatórios agora podem ser baixados em .xlsx",
        "icon": "ti-file-spreadsheet"
      },
      "cta": {
        "text": "Testar agora",
        "url": "/relatorios?demo=export",
        "new_tab": false
      },
      "rules": {
        "include": [
          { "mode": "starts_with", "pattern": "/relatorios" }
        ],
        "exclude": [
          { "mode": "starts_with", "pattern": "/relatorios/admin" }
        ]
      }
    }
  ]
}
```

**Critérios para incluir um banner na resposta:**
- `enabled = true`
- changelog está com `status = published`
- `expires_at` é null ou maior que `now()`

**Headers HTTP:**
- `Cache-Control: public, max-age=300` (5 min)
- `ETag` baseado no hash da resposta
- `Access-Control-Allow-Origin: *` (ou configurado pelo cliente)

### 7.2 Registrar evento

```
POST /api/v1/widget/{token}/contextual/event
```

**Body:**

```json
{
  "banner_id": 42,
  "reader_id": "uuid-do-leitor",
  "event": "shown" | "dismissed" | "clicked",
  "url": "https://app.cliente.com/relatorios/abc",
  "ts": "2026-06-26T14:33:00Z"
}
```

**Resposta:** `204 No Content` (fire-and-forget no client; sem corpo).

**Rate limiting:** `throttle:60,1` por IP + token (60 eventos/minuto).

---

## 8. Estrutura de código no Laravel

### 8.1 Models e relacionamentos

```php
// app/Models/ContextualBanner.php
class ContextualBanner extends Model
{
    protected $table = 'changelog_contextual_banners';
    protected $casts = [
        'enabled' => 'boolean',
        'cta_new_tab' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function changelog() {
        return $this->belongsTo(Changelog::class);
    }

    public function rules() {
        return $this->hasMany(ContextualRule::class, 'banner_id');
    }

    public function includeRules() {
        return $this->rules()->where('type', 'include');
    }

    public function excludeRules() {
        return $this->rules()->where('type', 'exclude');
    }

    public function isActive(): bool {
        return $this->enabled
            && ($this->expires_at === null || $this->expires_at->isFuture())
            && $this->changelog->status === 'published';
    }
}
```

### 8.2 Controllers

```php
// app/Http/Controllers/Widget/ContextualController.php
class ContextualController extends Controller
{
    public function index(string $token)
    {
        $account = Account::where('widget_token', $token)->firstOrFail();

        $banners = Cache::remember(
            "novidda:contextual:{$token}",
            300, // 5 minutos
            fn() => $account->changelogs()
                ->where('status', 'published')
                ->with(['contextualBanner.rules'])
                ->whereHas('contextualBanner', fn($q) =>
                    $q->where('enabled', true)
                      ->where(fn($q) => $q->whereNull('expires_at')
                                          ->orWhere('expires_at', '>', now()))
                )
                ->get()
                ->map(fn($changelog) => $this->serialize($changelog))
        );

        return response()->json(['banners' => $banners])
            ->header('Cache-Control', 'public, max-age=300');
    }

    public function event(Request $request, string $token)
    {
        $validated = $request->validate([
            'banner_id' => 'required|integer|exists:changelog_contextual_banners,id',
            'reader_id' => 'required|string|max:64',
            'event' => 'required|in:shown,dismissed,clicked',
            'url' => 'required|url|max:1000',
        ]);

        $account = Account::where('widget_token', $token)->firstOrFail();

        WidgetEvent::create([
            'account_id' => $account->id,
            'reader_id' => $validated['reader_id'],
            'type' => 'contextual_' . $validated['event'],
            'metadata' => ['banner_id' => $validated['banner_id'], 'url' => $validated['url']],
        ]);

        return response()->noContent();
    }

    private function serialize($changelog) { /* ... */ }
}
```

### 8.3 Rotas

```php
// routes/api.php
Route::prefix('v1/widget/{token}')->middleware(['throttle:300,1'])->group(function () {
    // ... rotas do sino já existentes
    Route::get('contextual', [ContextualController::class, 'index']);
    Route::post('contextual/event', [ContextualController::class, 'event'])
        ->middleware('throttle:60,1');
});
```

### 8.4 Form Request para criação/edição

```php
// app/Http/Requests/StoreContextualBannerRequest.php
class StoreContextualBannerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'enabled' => 'required|boolean',
            'style' => 'required|in:toast,top_bar,bottom_bar',
            'position' => 'required_if:style,toast|in:bottom_right,bottom_left,top_right,top_left',
            'frequency' => 'required|in:once_per_user,until_clicked,times_capped',
            'frequency_cap' => 'required_if:frequency,times_capped|nullable|integer|min:1|max:50',
            'auto_dismiss_seconds' => 'nullable|integer|min:1|max:300',
            'expires_at' => 'nullable|date|after:now',
            'custom_copy' => 'nullable|string|max:500',
            'cta_text' => 'nullable|string|max:80',
            'cta_url' => 'nullable|url|max:500',
            'cta_new_tab' => 'boolean',
            'rules' => 'required|array|min:1',
            'rules.*.type' => 'required|in:include,exclude',
            'rules.*.match_mode' => 'required|in:exact,contains,starts_with,regex',
            'rules.*.pattern' => 'required|string|max:500',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            // Validar regex se for o caso — não pode quebrar o matcher do client
            foreach ($this->input('rules', []) as $i => $rule) {
                if ($rule['match_mode'] === 'regex') {
                    if (@preg_match('/' . $rule['pattern'] . '/', '') === false) {
                        $v->errors()->add("rules.$i.pattern", 'Regex inválida.');
                    }
                }
            }
        });
    }
}
```

### 8.5 Service de cache

```php
// app/Services/ContextualBannerCache.php
class ContextualBannerCache
{
    public static function invalidate(string $accountToken): void
    {
        Cache::forget("novidda:contextual:{$accountToken}");
    }
}

// Disparado nos eventos:
// - Changelog::saved (status mudou para published)
// - ContextualBanner::saved
// - ContextualBanner::deleted
// - ContextualRule::saved/deleted
```

---

## 9. Implementação do client (Vanilla JS)

### 9.1 Estrutura de arquivos

```
public/widget/
  ├─ widget.js              (loader do sino, já existente)
  ├─ widget-contextual.js   (novo módulo desta feature)
  └─ widget.css             (já existente, dentro do shadow)
```

### 9.2 Loader carrega o módulo contextual condicionalmente

No `widget.js` (sino), após a renderização inicial, verifica se a conta tem banners contextuais ativos. Essa info pode vir como flag no endpoint `/unread-count` ou ser uma chamada separada.

```javascript
// widget.js (trecho relevante, conceitual)
async function init() {
  // ... renderização do sino ...

  const meta = await fetch(`${API}/widget/${TOKEN}/meta`).then(r => r.json());

  if (meta.has_contextual_banners) {
    // Carrega módulo separado, lazy
    const script = document.createElement('script');
    script.src = `${ASSET_BASE}/widget-contextual.js?v=${meta.contextual_version}`;
    script.async = true;
    document.head.appendChild(script);
  }
}
```

### 9.3 Módulo `widget-contextual.js`

Estrutura simplificada do módulo:

```javascript
(function () {
  const TOKEN = window.__noviddaConfig.token;
  const API = window.__noviddaConfig.apiBase;
  const READER = getOrCreateReaderId();

  let banners = [];
  let currentBanner = null;

  // 1. Buscar banners ativos
  fetch(`${API}/widget/${TOKEN}/contextual`)
    .then(r => r.json())
    .then(data => {
      banners = data.banners || [];
      runMatcher();
      setupRouteListener();
    });

  // 2. Matcher — roda contra a URL atual
  function runMatcher() {
    const url = window.location.pathname + window.location.search;
    const match = banners.find(b => matches(b, url) && shouldShow(b));
    if (match && match !== currentBanner) {
      hideCurrent();
      render(match);
      currentBanner = match;
    }
  }

  function matches(banner, url) {
    const hasInclude = banner.rules.include.some(r => testRule(r, url));
    if (!hasInclude) return false;
    const isExcluded = banner.rules.exclude.some(r => testRule(r, url));
    return !isExcluded;
  }

  function testRule(rule, url) {
    switch (rule.mode) {
      case 'exact': return url === rule.pattern;
      case 'contains': return url.includes(rule.pattern);
      case 'starts_with': return url.startsWith(rule.pattern);
      case 'regex':
        try { return new RegExp(rule.pattern).test(url); }
        catch (e) { return false; } // regex inválido — falha graciosa
    }
  }

  function shouldShow(banner) {
    const state = readState(banner.id);
    if (banner.frequency === 'once_per_user') return state.shown_count < 1;
    if (banner.frequency === 'until_clicked') return !state.clicked;
    if (banner.frequency === 'times_capped') return state.shown_count < banner.frequency_cap;
    return true;
  }

  // 3. Renderização no Shadow DOM
  function render(banner) {
    const host = document.createElement('div');
    host.id = 'novidda-ctx-root';
    document.body.appendChild(host);
    const shadow = host.attachShadow({ mode: 'closed' });
    shadow.innerHTML = template(banner);

    shadow.querySelector('.dismiss').addEventListener('click', () => {
      sendEvent(banner.id, 'dismissed');
      host.remove();
      bumpState(banner.id, { dismissed: true });
    });

    const cta = shadow.querySelector('.cta');
    if (cta) cta.addEventListener('click', (e) => {
      sendEvent(banner.id, 'clicked');
      bumpState(banner.id, { clicked: true });
      // navegação acontece normalmente
    });

    if (banner.auto_dismiss_seconds) {
      setTimeout(() => host.remove(), banner.auto_dismiss_seconds * 1000);
    }

    sendEvent(banner.id, 'shown');
    bumpState(banner.id, { shown_count: readState(banner.id).shown_count + 1 });
  }

  // 4. Listener de mudança de rota (SPA)
  function setupRouteListener() {
    const wrap = (fnName) => {
      const orig = history[fnName];
      history[fnName] = function () {
        const r = orig.apply(this, arguments);
        window.dispatchEvent(new Event('novidda:locationchange'));
        return r;
      };
    };
    wrap('pushState');
    wrap('replaceState');
    window.addEventListener('popstate', () =>
      window.dispatchEvent(new Event('novidda:locationchange')));
    window.addEventListener('novidda:locationchange', runMatcher);
  }

  // 5. Estado local
  function readState(bannerId) {
    try {
      return JSON.parse(localStorage.getItem(`novidda:ctx:${TOKEN}:${bannerId}`))
        || { shown_count: 0, clicked: false, dismissed: false };
    } catch (e) {
      return { shown_count: 0, clicked: false, dismissed: false };
    }
  }

  function bumpState(bannerId, patch) {
    try {
      const cur = readState(bannerId);
      localStorage.setItem(
        `novidda:ctx:${TOKEN}:${bannerId}`,
        JSON.stringify({ ...cur, ...patch, last_shown_at: new Date().toISOString() })
      );
    } catch (e) { /* localStorage disabled — fail silent */ }
  }

  // 6. Reader ID
  function getOrCreateReaderId() {
    let id = localStorage.getItem('novidda:reader_id');
    if (!id) {
      id = 'r_' + Math.random().toString(36).slice(2) + Date.now().toString(36);
      try { localStorage.setItem('novidda:reader_id', id); } catch (e) {}
    }
    return id;
  }

  // 7. Eventos
  function sendEvent(bannerId, event) {
    fetch(`${API}/widget/${TOKEN}/contextual/event`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        banner_id: bannerId,
        reader_id: READER,
        event,
        url: window.location.pathname,
        ts: new Date().toISOString(),
      }),
      keepalive: true, // sobrevive a navegação
    }).catch(() => {});
  }

  function hideCurrent() {
    const existing = document.getElementById('novidda-ctx-root');
    if (existing) existing.remove();
  }

  function template(banner) {
    const styleClass = `style-${banner.style}`;
    const posClass = banner.style === 'toast' ? `pos-${banner.position}` : '';
    return `
      <style>${BANNER_CSS}</style>
      <div class="novidda-banner ${styleClass} ${posClass}">
        <div class="content">
          <div class="title">${escape(banner.copy.title)}</div>
          ${banner.copy.description ? `<div class="desc">${escape(banner.copy.description)}</div>` : ''}
          ${banner.cta ? `<a class="cta" href="${escape(banner.cta.url)}"${banner.cta.new_tab ? ' target="_blank" rel="noopener"' : ''}>${escape(banner.cta.text)}</a>` : ''}
        </div>
        <button class="dismiss" aria-label="Fechar">×</button>
      </div>
    `;
  }

  function escape(s) {
    return String(s).replace(/[&<>"']/g, c => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[c]));
  }
})();
```

### 9.4 CSS do banner (dentro do Shadow DOM)

```css
:host {
  all: initial;
}
.novidda-banner {
  position: fixed;
  font-family: -apple-system, BlinkMacSystemFont, 'Inter', sans-serif;
  z-index: 2147483646;
  /* tema neumórfico vindo da config da conta */
  background: var(--novidda-bg, #F5F5F0);
  color: var(--novidda-ink, #16213E);
  box-shadow: 8px 8px 16px rgba(0,0,0,0.08), -4px -4px 12px rgba(255,255,255,0.6);
}

/* Toast — 320px nos cantos */
.style-toast {
  width: 320px;
  padding: 16px 20px;
  border-radius: 16px;
}
.pos-bottom_right { bottom: 20px; right: 20px; }
.pos-bottom_left  { bottom: 20px; left: 20px; }
.pos-top_right    { top: 20px; right: 20px; }
.pos-top_left     { top: 20px; left: 20px; }

/* Barras */
.style-top_bar, .style-bottom_bar {
  left: 0; right: 0;
  padding: 12px 20px;
  display: flex; align-items: center; gap: 12px;
}
.style-top_bar    { top: 0; }
.style-bottom_bar { bottom: 0; }

.dismiss {
  background: transparent;
  border: 0;
  font-size: 20px;
  cursor: pointer;
  color: var(--novidda-ink);
}

/* Animação suave */
.novidda-banner {
  animation: novidda-in 200ms ease-out;
}
@keyframes novidda-in {
  from { transform: translateY(10px); opacity: 0; }
  to   { transform: translateY(0);    opacity: 1; }
}
```

### 9.5 Offset para não sobrepor o sino

Quando o estilo for `toast` e a posição for `bottom_right` (ou `bottom_left`) — mesma posição do sino — adicionar offset automático:

```javascript
if (banner.style === 'toast' && banner.position.startsWith('bottom')) {
  // Sino tem ~70px de altura + 20px margin = ~90px
  // Posiciona o toast acima do sino
  shadow.querySelector('.novidda-banner').style.bottom = '110px';
}
```

---

## 10. Analytics — o que o cliente vê no admin

### 10.1 Métricas por banner

Dentro da tela do changelog, abaixo das estatísticas do widget tradicional, exibir um bloco **"Banner contextual"**:

| Métrica | Cálculo |
|---|---|
| Impressões | `count(events where type=contextual_shown and banner_id=X)` |
| Usuários únicos impactados | `count(distinct reader_id where type=contextual_shown)` |
| Taxa de clique (CTR) | `clicked / shown * 100` |
| Taxa de dispensa | `dismissed / shown * 100` |
| Tempo médio até dispensa | média de `dismissed_at - shown_at` |
| URLs mais frequentes | top 5 `url` agrupado |

### 10.2 Funil visual

```
Impressões  ────────  N
       │
       ▼
Cliques no CTA ────  M  (CTR: X%)
       │
       ▼
[opcional Fase 3] Adoção real ─ (cruzado com analytics próprio do cliente)
```

### 10.3 Comparativo

No dashboard geral, comparar performance de releases com vs sem banner contextual ativado — argumento de retenção para o plano que inclui essa feature.

---

## 11. Casos de borda e cuidados

### 11.1 Validação de regex

- Toda regex passada pelo cliente é validada **no backend** (PHP) e **no frontend** (`new RegExp(...)` em try/catch) antes de ser aplicada.
- No client, regex inválida falha silenciosamente — o banner simplesmente não aparece.
- Considerar timeout de execução para regex catastróficas (regex bomb): no servidor, usar `preg_match` com limite de backtracking; no client, evitar regex complexas via UI (validar tamanho do padrão).

### 11.2 Sanitização do conteúdo

- Título, descrição, texto do CTA: sempre escapados na renderização (`escape()` no exemplo).
- URLs do CTA: validar protocolo (apenas `http`, `https`, `mailto`, `tel`) — bloquear `javascript:`.

### 11.3 Throttle de impressões

Se o usuário navega rapidamente entre URLs que batem na regra (ex: navegação rápida em SPA), evitar múltiplas impressões consecutivas:

```javascript
let lastShown = 0;
function runMatcher() {
  if (Date.now() - lastShown < 1000) return; // mínimo 1s entre impressões
  // ...
  lastShown = Date.now();
}
```

### 11.4 Múltiplos banners válidos para a mesma URL

Se 2+ banners batem para a mesma URL ao mesmo tempo, mostrar apenas o **mais recente** (publicado mais recentemente). Documentar isso claramente para o cliente — não empilhar banners.

### 11.5 Z-index

`z-index: 2147483646` (um a menos que o máximo) tenta ficar acima de modais comuns. Não usar `2147483647` (máximo) para deixar espaço caso o cliente realmente precise sobrepor.

### 11.6 Acessibilidade

- `role="status"` ou `role="alertdialog"` no container do banner.
- Botão de dispensa com `aria-label="Fechar"`.
- Suporte a tecla `Esc` para dispensar.
- Foco gerenciado: não roubar foco automático (evita interromper digitação).

### 11.7 Modo de teste / preview

- Botão "Pré-visualizar" no admin abre modal renderizando o banner com os dados atuais — não vai pro servidor, é só HTML local.
- Opcional: parâmetro de query `?novidda_preview=BANNER_ID` que força o banner a aparecer mesmo se já tiver sido dispensado (apenas para o usuário Novidda autenticado).

### 11.8 Banner expirado / desativado em cache

Quando um banner expira ou é desativado:
- Cache do servidor é invalidado imediatamente.
- O client tem TTL próprio (cache HTTP do `/contextual`). Pior caso: o banner pode aparecer por mais 5 minutos após expirar. Aceitável.
- Para mudanças críticas, o cliente pode forçar refresh do widget.

---

## 12. Testes

### 12.1 Testes de backend (PHPUnit)

```php
// tests/Feature/ContextualBannerTest.php
- it_returns_active_banners_for_a_token
- it_excludes_disabled_banners
- it_excludes_expired_banners
- it_excludes_banners_of_unpublished_changelogs
- it_caches_the_response_for_5_minutes
- it_invalidates_cache_when_banner_is_saved
- it_validates_regex_patterns_on_save
- it_records_events_for_shown_dismissed_clicked
- it_rate_limits_event_endpoint
```

### 12.2 Testes de frontend (manuais, eventualmente automatizados)

- Matcher: cada `match_mode` testado contra URLs reais.
- Listener de SPA: navegação via `pushState` dispara matcher.
- Frequência: `once_per_user`, `times_capped` e `until_clicked` se comportam corretamente após reload.
- Shadow DOM: CSS do cliente não afeta o banner e vice-versa.
- Performance: medir LCP/CLS com Lighthouse antes e depois do widget — diferença ≤ 5%.
- Acessibilidade: testar com leitor de tela, navegação por teclado, foco.

### 12.3 Matriz de browsers

Suportar: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+ (mesma base do widget principal). Sem suporte a IE.

---

## 13. Roadmap de entrega

### Sprint 1 — Base de dados e API
- Migrations das tabelas `changelog_contextual_banners` e `changelog_contextual_rules`.
- Models, relacionamentos, factory.
- Endpoint `GET /contextual` com cache.
- Endpoint `POST /contextual/event`.
- Validação de regras (incluindo regex).
- Testes de backend.

### Sprint 2 — Painel administrativo
- Seção colapsada no formulário de changelog.
- CRUD de regras de URL (componente jQuery dinâmico).
- Pré-visualização do banner (modal).
- Validação client-side de regex.
- Indicador visual no listing de changelogs quando o banner está ativo.

### Sprint 3 — Módulo client (Vanilla JS)
- `widget-contextual.js` com matcher, renderização, eventos.
- Carregamento lazy a partir do `widget.js`.
- Shadow DOM e CSS isolado.
- Suporte a SPAs (interceptação de `pushState`).
- Persistência local de frequência.
- Build minificado + servido com cache HTTP correto.

### Sprint 4 — Analytics e refinamento
- Bloco de métricas no admin (impressões, CTR, dispensas).
- Funil visual.
- Comparativo com vs sem banner.
- Testes end-to-end manuais em SaaS de exemplo.
- Documentação para o cliente (como configurar).

**Estimativa total:** 4 sprints (~2 meses com 1 dev + meia mão de QA/design).

---

## 14. Próximos passos imediatos

1. Validar o desenho do schema com o restante do plano do Novidda — em especial a relação 1:1 entre `changelog` e `contextual_banner` (ou se faz sentido permitir N banners por changelog no futuro).
2. Definir como o cache será invalidado considerando o driver `file`/`database` (sem tags).
3. Decidir o design exato do toast e das barras em alinhamento com a identidade neumórfica do produto.
4. Confirmar a meta de tamanho do bundle (`widget-contextual.js` ≤ 8 KB gzip).
5. Iniciar Sprint 1.
