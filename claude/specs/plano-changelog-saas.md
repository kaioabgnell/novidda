# Novidda — Plano de Desenvolvimento

> Plataforma de Release Notes / Changelog (SaaS)

> **Tagline:** Mantenha seus usuários por dentro de features. Anuncie novos recursos no seu produto, promova engajamento e mantenha seus usuários atualizados sobre as mudanças — de maneira simples e sem esforço.

**Nome do projeto:** Novidda
**Repositório:** `git@github.com:kaioabgnell/novidda.git`

**Stack obrigatória:** PHP 8.1 · Laravel · Blade + HTML + CSS + JS (jQuery quando necessário) · Widget em Vanilla JS (zero dependências no cliente)
**Banco:** MySQL · **Cache:** apenas o cache nativo do Laravel · **Storage:** disco local do servidor (sem S3/AWS) · **Sem CDN e sem disparo de e-mail**
**Idioma:** sistema 100% em português (mercado brasileiro), com suporte a **modo escuro**
**Design:** Neumorfismo (soft UI) — sombras suaves e elementos com sensação tátil
**Premissa central:** performance absoluta — o widget não pode impactar o carregamento ou causar lentidão no sistema de quem integra.

---

## 1. Análise de mercado

### 1.1 Panorama do segmento

O mercado de "product communication / changelog widgets" amadureceu bastante. Hoje é uma categoria estabelecida dentro de *Product-Led Growth*, com players consolidados e uma demanda clara: empresas SaaS perdem retenção e adoção de features porque o usuário simplesmente **não sabe que a feature existe**. A dor que você identificou — *tirar o cliente do sistema para mostrar novidades* — é exatamente o problema que essa categoria resolve.

### 1.2 Concorrentes principais

| Produto | Posicionamento | Forças | Brechas (oportunidade pra você) |
|---|---|---|---|
| **Changecrab** (referência) | Simples, barato, focado em devs/indie | Setup rápido, embed leve, preço acessível | Analytics raso, pouca customização visual, sem reações/comentários robustos |
| **Beamer** | Widget completo + NPS + feedback | Muito completo, segmentação avançada | Pesado, caro, script pode impactar performance |
| **AnnounceKit** | Foco em widget e notificações | Boa segmentação, multi-idioma | UX datada, preço escala rápido |
| **Headway** | Minimalista, "what's new" | Muito leve e simples | Sem comentários, sem analytics profundo, quase sem interatividade |
| **LaunchNotes** | Enterprise, roadmap público | Robusto, integra com Jira/Linear | Caro, complexo demais pra PME |
| **Canny (changelog)** | Changelog acoplado a feedback board | Ecossistema feedback→roadmap→changelog | Changelog é secundário no produto |
| **Olvy / Released** | Modernos, IA pra gerar notas | UX moderna, IA generativa | Ainda jovens, preço premium |

### 1.3 Posicionamento recomendado

Há um espaço claro no meio do mercado: **a leveza e o preço do Changecrab/Headway, com a interatividade do Beamer (reações, comentários) e uma identidade visual moderna** com estética neumórfica (soft UI). A maioria dos concorrentes tem ou UX datada ou peso excessivo no script. Seu diferencial técnico declarado — **lazy loading total e impacto zero na performance** — é um argumento de venda real, especialmente pra clientes preocupados com Core Web Vitals. Foco no **mercado brasileiro**, com interface 100% em português.

**Proposta de valor em uma frase:** *o widget de changelog mais leve do mercado, com a estética tátil e sofisticada do neumorfismo e interatividade de verdade (reações e comentários moderados).*

### 1.4 Funcionalidades modernas que valem a pena ter

Além do que você já listou, estas funcionalidades aparecem nos players modernos e diferenciam o produto (todas viáveis dentro das premissas: sem e-mail, sem CDN, storage local):

- **Geração de notas com IA** — a partir de um título + tópicos, gerar a descrição em tom amigável. Forte apelo, baixo custo de implementação via API.
- **Página pública de changelog** — um `seusistema.com.br/sua-conta` gerado automaticamente, indexável (bom pra SEO do cliente).
- **Feed RSS / JSON** — assinatura técnica do changelog, útil pra integrações.
- **Webhook ao publicar** — dispara um POST pra uma URL configurada pelo cliente (substitui a notificação por e-mail respeitando a premissa).
- **Labels de "Novo" com expiração** — destaque visual que some após X dias.
- **Modo "boosted" / pinned** — fixar um anúncio importante no topo.
- **Score de engajamento por release** — combinar views + reações + comentários numa métrica única.
- **Modo escuro nativo** — no painel e no widget, com toggle e detecção automática da preferência do sistema.

> Funcionalidades que dependeriam de infra externa foram conscientemente deixadas de fora por decisão de projeto: notificação por **e-mail** para assinantes e **CDN** global. A comunicação proativa fica a cargo do **webhook**.

---

## 2. Visão geral da arquitetura

O sistema tem **três componentes** que conversam via API:

1. **Painel administrativo** (Laravel + Blade + HTML/CSS/JS, jQuery quando necessário) — onde o cliente gerencia conta, changelogs, categorias, widget e analytics. Sem SPA, sem Vue/React/Inertia/Livewire.
2. **API pública do widget** — endpoints altamente otimizados e cacheados (cache nativo do Laravel), consumidos pelo script embeddado.
3. **Widget embeddable** (Vanilla JS) — script único por conta, injetado no sistema do cliente.

```
[Sistema do cliente] --carrega--> [widget.js (único por conta)]
                                         |
                                  (só ao clicar) GET /api/v1/widget/{token}/feed
                                         |
                          [API pública cacheada (cache do Laravel)]
                                         |
                                    [Banco MySQL]
                                         ^
                                         |
[Cliente] --gerencia--> [Painel Admin Laravel + Blade] --escreve-->
```

### 2.1 Stack técnica detalhada

| Camada | Tecnologia | Justificativa |
|---|---|---|
| Backend | PHP 8.1 + Laravel 10 | Premissa do projeto. Laravel 10 é o último compatível com PHP 8.1. |
| Admin (frontend) | Blade + HTML + CSS + JS (jQuery quando necessário) | Premissa do projeto. Sem build de SPA, sem Vue/React/Inertia/Livewire. Renderização server-side com Blade; interatividade pontual com JS/jQuery. |
| Widget | Vanilla JS puro (sem framework, sem jQuery) | **Crítico:** zero dependências no ambiente do cliente — o widget não pode carregar jQuery nem nada externo. |
| Banco | MySQL 8 | Premissa do projeto. Configs do widget guardadas em colunas `JSON` do MySQL. |
| Cache | Cache nativo do Laravel | Premissa do projeto. Driver `file` ou `database` (sem Redis). Usado pro feed do widget, contadores e rate limiting. |
| Filas | Laravel Queue (driver `database` ou `sync`) | Processamento assíncrono leve (ex: geração de thumbnails, webhooks). Sem Redis. |
| Storage | **Disco local do servidor** (`storage/app/public`) | Premissa do projeto. Imagens dos changelogs salvas localmente via `Storage::disk('public')`, com `php artisan storage:link`. Sem S3/AWS. |
| CDN | **Nenhuma** | Premissa do projeto. `widget.js` e assets servidos direto pelo próprio servidor. |
| E-mail | **Nenhum disparo** | Premissa do projeto. Comunicação proativa via webhook. Reset de senha pode usar log/exibição em tela ou ser tratado manualmente — ver seção de autenticação. |

> **Atenção à premissa de performance sem CDN:** como o `widget.js` será servido pelo próprio servidor, capriche no cache HTTP (headers `Cache-Control`/`ETag`) e no versionamento do arquivo, além de minificação e gzip/brotli no servidor web (Nginx/Apache). Isso compensa boa parte da ausência de CDN.

### 2.2 Configuração de ambiente (`.env`)

O banco de dados **já está criado** (vazio, aguardando as migrations). Configuração local para o `.env` do projeto:

```dotenv
APP_NAME=Novidda

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=novidda_local
DB_USERNAME=root
DB_PASSWORD=

# Premissas do projeto: cache e storage locais, sem e-mail
CACHE_DRIVER=file
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
SESSION_DRIVER=file
```

Próximo passo após preencher o `.env`: rodar `php artisan key:generate`, criar as migrations (seção 5) e executar `php artisan migrate` + `php artisan storage:link`.

> **Atenção:** o `.env` contém credenciais e **nunca** deve ser commitado. Garanta que `.env` está no `.gitignore` (o Laravel já inclui por padrão) e versione apenas o `.env.example` sem a senha real.

---

## 3. O Widget — especificação de performance (núcleo do produto)

Este é o componente mais sensível. As decisões aqui definem o diferencial competitivo.

### 3.1 Estratégia de carregamento (lazy loading total)

O script inicial deve ser **mínimo** (loader). Nada do conteúdo real é carregado até a interação.

**Etapa 1 — loader (carrega com a página, ~2-4 KB minificado):**
- Injeta apenas o botão/sino e o badge de contagem.
- A contagem de não-lidos vem de **uma única requisição leve e cacheada** (`GET /api/v1/widget/{token}/unread-count?reader_id=...`) que retorna só um número — ou, ainda mais leve, é embutida no próprio snippet via cache headers e atualizada por requisição assíncrona não-bloqueante.
- Carregamento `async` / `defer`, fora do critical path.

**Etapa 2 — ao clicar no sino pela primeira vez:**
- Aí sim carrega o CSS do painel, o JS do conteúdo e faz a requisição do feed (`GET /api/v1/widget/{token}/feed`).
- Tudo carregado **sob demanda** — imagens com `loading="lazy"`, vídeos do YouTube como thumbnail clicável (facade pattern: só carrega o iframe pesado do YouTube ao clicar no play).

### 3.2 Comportamento do badge de não-lidos

- Mostra a quantidade de novidades não lidas (ex: balão vermelho com "3").
- **Ao clicar uma vez:** o balão zera e some, restando apenas o texto (default "Novidades", customizável).
- O estado de "lido" é persistido por leitor — via `reader_id` (passado pelo cliente no snippet) ou, na ausência dele, via `localStorage` + cookie de fallback.
- O contador volta a aparecer quando uma **nova** release é publicada depois da última visita.

### 3.3 Opções de exibição e posicionamento

- **Modos de abertura:** painel lateral (slide da direita/esquerda) **ou** dropdown de cima para baixo (ancorado no sino).
- **Posição:** esquerda ou direita da tela.
- Configurável no painel, refletido via JSON de config carregado pelo widget.

### 3.4 Customização

- Texto do botão ("Novidades" → qualquer texto).
- Cores, fontes, posição via painel.
- **CSS customizado** — campo livre onde o cliente injeta CSS que sobrescreve o tema padrão do widget (escopado num shadow DOM ou prefixo de classe pra não vazar pro sistema dele).

> **Recomendação técnica:** renderizar o widget dentro de um **Shadow DOM**. Isola completamente o CSS do widget do CSS do sistema do cliente (e vice-versa), eliminando conflitos de estilo — problema #1 de widgets embeddados.

### 3.5 Interatividade

- **Reações por emoji:** cada release pode ter um emoji default (coração ❤️) ou um emoji específico definido pelo autor. O usuário final reage com um clique.
- **Comentários:** usuário final pode escrever comentários. Comentários entram como *pendentes* e só ficam visíveis após **aprovação/rejeição** pelo dono da conta.
- Configurável por changelog: mostrar comentários, permitir novos comentários, mostrar reações.
- **Botão de ação (CTA):** texto, URL, cor (hex ou nome CSS), abrir em nova aba — exibido ao final do changelog.

---

## 4. Painel administrativo — telas

### 4.1 Autenticação
- **Login** (e-mail + senha, "lembrar-me").
- **Cadastro** (nome, e-mail, senha, nome da empresa/produto).
- **Esqueceu a senha** — fluxo de reset usando o `Password Broker` nativo do Laravel. **Como não há disparo de e-mail**, o token de reset precisa de uma estratégia alternativa: registrar o link em log (`mail.log`) durante o desenvolvimento e, em produção, optar por um destes caminhos — (a) integrar um SMTP simples no futuro se a premissa mudar, ou (b) reset assistido pelo suporte/admin. **Decisão a confirmar com você.**
- Verificação de e-mail: desabilitada (depende de envio de e-mail, que está fora do escopo).

### 4.2 Dashboard / Analytics
- Quantas pessoas clicaram/abriram o widget.
- Views por changelog, usuários únicos, taxa de leitura.
- Reações e comentários por release.
- Gráfico de engajamento ao longo do tempo.
- Top releases por engajamento.

### 4.3 Gestão de Changelogs / Releases
Listagem com ações: **Rascunho · Arquivar · Editar · Remover · Publicar**.

**Formulário "Novo changelog":**
- Título
- Descrição (editor rich text — recomendo Quill ou Trumbowyg, que funcionam com JS/jQuery puro, sem build de SPA)
- **Tipo:** Feature · Hotfix · Melhoria · Anúncio
- **Status:** Rascunho · Publicado · Arquivado
- **Categorias** (múltipla seleção)
- **Mídia:** upload de fotos, embed de vídeo do YouTube
- **Emoji da reação** (default ❤️, customizável por release)

**Configurações do Widget (por changelog):**
- [ ] Dispara webhook ao publicar este changelog *(substitui o e-mail para assinantes — fora do escopo)*
- [ ] Mostrar seção de comentários no widget
- [ ] Permitir adicionar novos comentários
- [ ] Mostrar reações no widget

**Botão de ação no widget (por changelog):**
- Texto do botão
- URL do botão
- Cor do botão (hex ou nome CSS)
- [ ] Abrir em nova aba ao clicar

### 4.4 Cadastro de Tipos / Categorias
- CRUD de categorias (nome, cor, ícone opcional).
- Os "tipos" (Feature/Hotfix/Melhoria/Anúncio) podem ser fixos ou também editáveis — recomendo deixá-los editáveis pra flexibilidade.

### 4.5 Moderação de comentários
- Fila de comentários pendentes.
- Aprovar / Rejeitar.
- Só aparecem no widget após aprovação.

### 4.6 Código Embed
- Tela que gera o snippet único da conta.
- Script único por conta (token vinculado ao workspace — todos os dados ficam vinculados a ele).
- Botão "copiar" + instruções de instalação.

### 4.7 Configurações do Widget (globais da conta)
- Texto do botão.
- Modo de abertura (lateral / cima-baixo).
- Posição (esquerda / direita).
- Tema / cores (com suporte a modo escuro).
- CSS customizado.
- URL de webhook (disparada ao publicar — substitui a lista de e-mail).

---

## 5. Modelo de dados (schema inicial)

```
accounts (workspaces)
  id, name, slug, plan, widget_token (único), created_at

users
  id, account_id, name, email, password, email_verified_at

categories
  id, account_id, name, color, icon

changelogs
  id, account_id, title, slug, description (rich text/html),
  type (enum: feature|hotfix|improvement|announcement),
  status (enum: draft|published|archived),
  reaction_emoji (default '❤️'),
  published_at, created_at, updated_at

changelog_category (pivot)
  changelog_id, category_id

changelog_media
  id, changelog_id, type (image|youtube),
  url (YouTube) | path (caminho local em storage/app/public para imagens),
  position

changelog_widget_settings
  changelog_id, fire_webhook (bool),
  show_comments (bool), allow_comments (bool), show_reactions (bool),
  cta_text, cta_url, cta_color, cta_new_tab (bool)

widget_settings (global por conta)
  account_id, button_text (default 'Novidades'),
  open_mode (side|dropdown), position (left|right),
  theme (JSON — inclui preferência de modo escuro), custom_css (text),
  webhook_url (nullable)

reactions
  id, changelog_id, reader_id, emoji, created_at

comments
  id, changelog_id, reader_id, author_name, body,
  status (enum: pending|approved|rejected), created_at

reads (controle de não-lidos)
  id, account_id, reader_id, changelog_id, read_at

widget_events (analytics)
  id, account_id, changelog_id (nullable), reader_id,
  type (open|view|reaction|comment), created_at
```

> **Multi-tenancy:** todo dado é escopado por `account_id`. Use um *global scope* no Eloquent pra garantir isolamento automático. O `widget_token` é a chave pública que liga o script à conta.
> **Imagens:** salvas no disco local (`storage/app/public/changelogs/...`) e servidas via symlink criado por `php artisan storage:link`. A coluna guarda o caminho relativo, não uma URL de S3.
> **JSON no MySQL:** as configs de tema/widget usam o tipo `JSON` nativo do MySQL 8 — sem necessidade de Postgres/JSONB.

---

## 6. API pública do widget (otimizada)

| Endpoint | Método | Cache | Descrição |
|---|---|---|---|
| `/api/v1/widget/{token}/config` | GET | Longo (cache Laravel + header HTTP) | Config visual do widget. |
| `/api/v1/widget/{token}/unread-count` | GET | Curto | Só o número de não-lidos. |
| `/api/v1/widget/{token}/feed` | GET | Médio (cache Laravel) | Feed de changelogs publicados. |
| `/api/v1/widget/{token}/read` | POST | — | Marca como lido. |
| `/api/v1/widget/{token}/reaction` | POST | — | Registra reação. |
| `/api/v1/widget/{token}/comment` | POST | — | Cria comentário (pendente). |

**Otimizações obrigatórias:**
- Cache do feed via cache nativo do Laravel (driver `file` ou `database`), com invalidação ao publicar/editar.
- `ETag` / `Cache-Control` nas respostas pro browser não re-baixar (compensa a ausência de CDN).
- Rate limiting por token (middleware `throttle` do Laravel).
- Respostas mínimas (só campos necessários, sem over-fetching).
- CORS configurado pra aceitar o domínio do cliente.
- Minificação + gzip/brotli no servidor web para `widget.js` e assets.

---

## 7. Identidade visual — Neumorfismo (Soft UI)

A direção de design é o **Neumorfismo**: a evolução do *flat design* que reintroduz profundidade de forma sutil. Combina o realismo do **esquemorfismo** (elementos que imitam objetos do mundo real — um botão que parece um botão físico apertável) com a **simplicidade** do design moderno. Sombras suaves e gradientes discretos criam elementos que parecem *elevados* (em relevo) ou *embutidos* (afundados) na superfície — uma sensação tátil e refinada. É a estética de apps de fintech e produtividade (Apple, Stripe, Flow Ninja), onde clareza encontra polimento.

### 7.1 Como o neumorfismo funciona na prática

O efeito vem de **duas sombras** em cada elemento — uma clara e uma escura — projetadas em direções opostas, sobre um fundo de cor próxima à do elemento:

```css
:root {
  --bg: #e0e5ec;            /* fundo base — cinza-azulado claro */
  --shadow-dark: #a3b1c6;   /* sombra inferior-direita */
  --shadow-light: #ffffff;  /* luz superior-esquerda */
}

/* elemento elevado (em relevo) */
.neu-raised {
  background: var(--bg);
  border-radius: 16px;
  box-shadow: 8px 8px 16px var(--shadow-dark),
             -8px -8px 16px var(--shadow-light);
}

/* elemento embutido (afundado) — para inputs e estados "pressionado" */
.neu-inset {
  background: var(--bg);
  border-radius: 16px;
  box-shadow: inset 6px 6px 12px var(--shadow-dark),
              inset -6px -6px 12px var(--shadow-light);
}
```

### 7.2 Tokens e princípios

- **Fundo monocromático** — todo o layout vive sobre uma única cor base (claro: `#e0e5ec`; escuro: `#2d3138`). Os elementos não têm cor de fundo diferente — eles "emergem" da superfície via sombra.
- **Cantos generosos** — `border-radius` de 12–20px é parte da linguagem.
- **Cor de marca pontual** — usar a cor de destaque (a definir) só em CTAs, ícones ativos e gráficos. O resto é monocromático.
- **Estados táteis** — botões em relevo no estado normal, afundados (`inset`) no `:active`/pressionado. Inputs sempre afundados.
- **Tipografia** — sans-serif legível (Inter, Poppins ou Nunito). Contraste de texto suficiente (cuidado: neumorfismo tende a baixo contraste — garantir acessibilidade WCAG AA).
- **Ícones** — usar **Font Awesome (versão free)** em toda a interface do sistema. **Não usar emojis como ícones de UI** (menus, botões, ações, status). Os emojis ficam restritos apenas ao recurso de **reação do usuário final** no widget (ex: ❤️), que é uma funcionalidade de conteúdo, não de interface.
- **Mobile-first** no painel e no widget.

### 7.3 Ícones — Font Awesome (free)

Toda a iconografia do sistema usa o **Font Awesome free** (Solid e Regular — os estilos disponíveis na versão gratuita). **Emojis não são usados como ícones de interface** em nenhum lugar do painel ou do widget.

- Instalação recomendada: via CDN local (baixar e servir do próprio servidor, respeitando a premissa de "sem CDN externa") ou via pacote/kit self-hosted.
- Uso padrão: `<i class="fa-solid fa-bell"></i>` para o sino do widget, `fa-solid fa-plus` para "novo changelog", `fa-solid fa-chart-line` para analytics, etc.
- Restringir-se aos ícones do plano free — evitar referências a ícones que só existem no Pro.
- **Única exceção:** os emojis continuam válidos como **reação de conteúdo** do usuário final no widget (coração por default, ou o emoji escolhido por release). Isso é conteúdo/interação, não iconografia de UI.

### 7.4 Modo escuro (obrigatório)

O neumorfismo se adapta bem ao dark mode — basta inverter a base e recalcular as duas sombras (a "luz" fica num cinza mais claro que a base, a "sombra" num tom mais escuro). Implementar via classe `.dark` no `<body>` + variáveis CSS, com toggle no painel e detecção de `prefers-color-scheme`. O widget também herda essa preferência.

```css
.dark {
  --bg: #2d3138;
  --shadow-dark: #20242a;
  --shadow-light: #3a3f47;
}
```

### 7.5 Padrões de layout SaaS

Seguindo as convenções de produtos SaaS modernos (estrutura, nomenclatura e fluxos consolidados na indústria):

- **Sidebar de navegação** à esquerda com ícones + labels: Dashboard, Changelogs, Categorias, Widget, Analytics, Configurações.
- **Topbar** com nome da conta, toggle de modo escuro e menu do usuário.
- **Dashboard** com *metric cards* (cards de métrica em relevo) no topo: total de aberturas, taxa de leitura, reações, comentários pendentes.
- **Empty states** bem cuidados — telas vazias que guiam o primeiro uso (ex: "Crie seu primeiro changelog").
- **Onboarding** em poucos passos: cadastro → criar primeiro changelog → copiar embed.
- **Nomenclatura** consolidada: "Publicar", "Rascunho", "Arquivar", "Embed code", "Workspace", "Analytics".
- **Tela de pricing/planos** (fase futura) no padrão de 3 colunas com plano destacado.

> **Cuidado com acessibilidade:** o neumorfismo é lindo mas pode pecar em contraste. Para textos e elementos interativos, garantir contraste mínimo WCAG AA — usar as sombras pra dar forma, mas nunca depender só delas pra comunicar estado (adicionar ícones/cor quando necessário).

---

## 8. Segurança e isolamento

- Token público do widget **read-mostly** — não expõe dados sensíveis.
- Escrita (reações/comentários) com rate limiting agressivo e validação anti-spam (honeypot, throttle por IP/reader).
- Comentários sempre moderados antes de publicar.
- CSS customizado sanitizado (evitar injeção via `expression()` / URLs maliciosas).
- Shadow DOM isola o widget do sistema hospedeiro.
- Isolamento multi-tenant por `account_id` em todas as queries.
- HTTPS obrigatório.

---

## 9. Roadmap por fases

### Fase 1 — MVP (validação)
- Autenticação completa (login, cadastro, esqueceu senha).
- CRUD de changelogs com tipo, status, categorias, mídia (imagem com upload local + YouTube).
- Geração de embed único por conta.
- Widget Vanilla JS: sino + badge de não-lidos + lazy loading total + 1 modo de abertura.
- Marcação de lido.
- Customização básica (texto do botão, posição, cor).
- Design neumórfico + modo escuro desde o início.

### Fase 2 — Engajamento
- Reações por emoji.
- Comentários + moderação (aprovar/rejeitar).
- Botão de ação (CTA) por changelog.
- Segundo modo de abertura (lateral + dropdown).
- Webhook ao publicar.
- Analytics (aberturas, views, taxa de leitura).
- CSS customizado.

### Fase 3 — Escala e diferenciais
- Página pública de changelog + SEO.
- Geração de notas com IA.
- Billing e planos (free/pro).
- Self-serve onboarding.
- Feed RSS/JSON.
- Labels de "Novo" com expiração e modo pinned.

---

## 10. Métricas de sucesso

- **Performance:** impacto no LCP/CLS do site do cliente próximo de zero (loader < 5 KB, conteúdo lazy).
- **Ativação:** % de contas que instalam o widget após cadastro.
- **Engajamento:** taxa de abertura do widget, reações e comentários por release.
- **Retenção:** contas ativas publicando regularmente.

---

## 11. Próximos passos sugeridos

1. Montar o schema de banco em migrations Laravel (MySQL).
2. Configurar storage local (`php artisan storage:link`) e o fluxo de upload de imagens.
3. Prototipar o **loader do widget** primeiro (é o coração da premissa de performance) e medir o impacto real — servido pelo próprio servidor, sem CDN.
4. Desenhar o design system neumórfico (variáveis CSS de fundo/sombras, claro/escuro, componentes base em Blade).
5. Construir o MVP da Fase 1.

> **Decisão pendente:** definir a estratégia do "esqueceu a senha" já que não há disparo de e-mail (ver seção 4.1) e escolher a cor de marca de destaque que vai pontuar o tema neumórfico. : essa sera uma feature futura. 

## estudo de mercado 
1. Funcionalidades Mais Importantes (O Feijão com Arroz Bem Feito)
Essas são as funcionalidades que o mercado já espera e que agregam valor direto ao usuário final e à equipe de produto.

Segmentação e Direcionamento de Público: Nem todo mundo precisa ver todo release. A capacidade de mostrar um changelog apenas para usuários de um plano específico, de uma região ou internos (BETA) é crucial.

Editor de Texto Rico e Multimídia: Suporte a Markdown, blocos de código (para produtos técnicos), GIFs, vídeos incorporados e imagens. Um changelog visual converte 3x mais atenção do que um textão.

Múltiplos Canais de Distribuição (Omnichannel): O changelog não deve morar só em uma página isolada. Ele precisa de:

Widget In-App (um sininho ou pop-up dentro do sistema).

E-mail (newsletter de produto automatizada).

Integração com Slack/Teams e redes sociais.

Reações e Coleta de Feedback: Botões de emoji (👍, ❤️, 😮) e um campo de comentário rápido para o usuário dizer o que achou da novidade.

Agendamento e Rascunhos: Permitir que o time de marketing/produto prepare o lançamento e agende a publicação para coincidir com o deploy.

2. Premissas para Fechamento (Deal Breakers)
Se a sua ferramenta não tiver isso, a equipe de segurança, compliance ou engenharia da empresa compradora vai vetar a contratação.

Customização Total (White-label & Custom Domain): O changelog precisa parecer que foi construído pela própria empresa. Deve aceitar CSS customizado, fontes da marca e rodar em updates.empresa.com.

Segurança e Governança (SSO e RBAC): Integração com provedores de identidade (Okta, Google Workspace) para o login dos funcionários e controle de permissões (quem pode escrever, quem pode apenas revisar e quem pode publicar).

Integrações Nativas com o Pipeline de Engenharia: Conexão direta com Jira, GitHub, GitLab e Linear. Se o PO tiver que copiar e colar o que foi feito manualmente, ele vai odiar a ferramenta. O sistema deve puxar os pull requests ou issues finalizados e sugerir o rascunho.

Privacidade e Changelogs Internos: Opção de criar um changelog protegido por senha ou token para clientes enterprise sob contrato de confidencialidade (NDA) ou para o time interno de vendas/CS.

3. Ideias Inovadoras e Diferenciais Competitivos
Para se destacar de concorrentes consolidados (como Beamer, Headway, LaunchNotes), é preciso ir além do "mural de avisos". Aqui estão ideias que o mercado está adotando ou que ainda são pouquíssimo exploradas:

O que os concorrentes avançados já têm (mas nem todos):
IA para Resumo e Tom de Voz (AI Copywriter): O sistema lê os commits técnicos do GitHub e os transforma em um texto amigável para o usuário final, adaptando o tom (divertido, corporativo, técnico).

Roadmap Conectado: O changelog é o fim da linha. O início é o Roadmap. Quando uma feature muda de "Em desenvolvimento" para "Concluído", o changelog é gerado quase automaticamente, notificando quem votou naquela ideia.

Sugestões Disruptivas (O "Oceano Azul" dos Changelogs):
Changelog Interativo com "Interactive Walkthroughs" (Guias de Tela): Em vez de apenas ler que um botão novo foi adicionado, o usuário clica em "Me mostre" no changelog e a ferramenta inicia um tour guiado (tour in-app) piscando o novo botão na tela dele.

ROI de Feature (Análise de Adoção Direta): Uma aba de analytics que cruza os dados de quem leu o changelog com o uso real da feature no banco de dados. Exemplo: "O changelog da Feature X teve 5.000 visualizações e gerou um aumento de 12% no uso da funcionalidade nas primeiras 48h".

Vídeos Curtos Gerados por IA (Estilo TikTok/Reels de Produto): A IA lê o texto do seu release, puxa os prints anexados e gera um vídeo de 15 segundos narrado com avatar virtual resumindo a atualização para o usuário dar play dentro do widget.

Changelog Dinâmico por Personas: Uma inteligência que entende o cargo do usuário logado. Se o usuário é o Financeiro, o changelog destaca melhorias em relatórios e notas fiscais. Se é o Desenvolvedor, destaca os novos endpoints da API.

---

## 12. Decisões de planejamento (finalizadas em 2026-06-25)

Decisões tomadas para destravar o início do desenvolvimento:

| Tema | Decisão |
|---|---|
| **Escopo do 1º ciclo** | Fase 1 MVP completa, entregue em etapas. |
| **Conta/usuário** | Multi-usuário por conta (equipe). Papel leve: `owner` / `editor` (sem RBAC completo no MVP — evoluível depois). |
| **Leitor do widget** | Identificação **anônima** via `localStorage` + cookie de fallback. `reader_id` explícito fica para fase futura. |
| **Editor rich text** | **Quill** (Vanilla JS, sem jQuery). Saída em HTML sanitizado. |
| **Feature extra incluída** | **Agendamento de publicação**: `published_at` no futuro + job na fila `database` que publica na hora marcada. |

### Fora de escopo (backlog/visão futura, vindos do estudo de mercado)
E-mail/newsletter, integração Slack/Teams, SSO, integração Jira/GitHub/Linear, vídeo gerado por IA, segmentação de público por persona/plano, IA para gerar notas, **reset de senha** (sem disparo de e-mail) e definição da **cor de marca** de destaque.

### Premissa de papéis (multi-usuário leve)
- `users.role` enum (`owner` | `editor`), default conforme o fluxo de cadastro (primeiro usuário = `owner`).
- `owner` gerencia conta/usuários/configurações; `editor` gerencia changelogs/categorias/moderação.
- Sem tela de gestão de equipe no MVP além do necessário; expansível para RBAC completo sem migração destrutiva.