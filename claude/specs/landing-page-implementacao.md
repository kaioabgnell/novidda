# Landing Page Novidda — Planejamento de Implementação

**Status:** implementado (2026-07-04) — pendente apenas medição Lighthouse e itens de fase 2
**Data:** 2026-07-04
**Objetivo:** substituir o redirect da rota `/` por uma landing page de alta conversão que apresente o Novidda como plataforma **100% gratuita** de anúncio de novidades in-app (changelog, roadmap, banners contextuais), leve, segura e sem impacto na performance do sistema do cliente.

## Regras obrigatórias

1. **Proibido usar emojis** em qualquer parte da landing (headings, cards, badges, botões, FAQ, footer, meta tags).
2. **Toda iconografia deve usar Font Awesome** (já disponível localmente em `public/vendor/fontawesome/`, mesmo pacote usado no painel) ou biblioteca similar de ícones vetoriais. Ícones sempre como `<i class="fa-solid fa-...">` (ou SVG inline), com `aria-hidden="true"` e texto acessível ao lado.
3. Ícones seguem a paleta do DESIGN.md: violet `#7B61FF` sobre superfícies claras, violet claro `#9D8FFF` sobre a banda dark — nunca cores fora dos tokens.

---

## 1. Contexto do produto (fatos verificados no código)

| Item | Realidade atual |
|---|---|
| Rota `/` | `routes/web.php:23` — redireciona para `login` (visitante) ou `dashboard` (autenticado). Não existe landing. |
| Stack | Laravel 10, PHP 8.1+, Blade + CSS custom (sem Tailwind no painel), widget em Vanilla JS |
| Auth | `GET /login` (rota `login`) e `GET /register` (rota `register`) |
| Logos | `public/img/Novidda_Logo.png` (principal), `public/img/Novidda_Logo_font_white.png` (fundos escuros), `public/favicon.ico` |
| CSS de referência | `public/css/neu.css` (variáveis de tema, dark mode via `body.dark`) |
| Fonte | Inter referenciada mas **não carregada como webfont** — a landing precisa carregar Inter explicitamente (incl. weight 900 para display) |
| Instalação do widget | 1 linha: `<script src="https://SEU_DOMINIO/widget.js" data-token="TOKEN" async></script>` |
| Performance do widget | Loader ~151 linhas, `async`, zero dependências, feed carregado sob demanda (lazy), sem CDN externo — impacto zero em Core Web Vitals |

### Funcionalidades a apresentar na landing (inventário completo)

1. **Feed de novidades (changelog)** — editor rich text, mídias, categorias, publicar/arquivar, status de leitura por usuário.
2. **Widget in-app embutível** — instalação em 1 linha, badge de não-lidos, personalização de posição/cor/ícone.
3. **Segmentação de audiência** — regras por plano, papel, empresa e atributos personalizados; estimativa de alcance e preview de audiência.
4. **Banner contextual** — anúncios disparados por contexto/evento na página, com contador regressivo.
5. **Roadmap público com votação** — votos, comentários e feedback dos usuários.
6. **Reações e comentários com moderação** — fila de aprovar/rejeitar.
7. **Analytics de engajamento** — visualizações, leituras, reações, eventos do widget, segmentado por audiência elegível.
8. **Multi-conta / multi-cliente** — contas ilimitadas, ideal para agências e quem atende vários clientes.
9. **Customização ampla** — aparência do widget, categorias, modo escuro.
10. **Feedback por changelog** — colete sentimento a cada anúncio.

### Mensagens-chave (pilares de copy)

- **100% gratuito** — sem plano pago, sem limite de contas, sem limite de clientes, sem cartão de crédito.
- **Não atrapalha a performance** — script assíncrono, zero dependências, carregamento sob demanda, impacto zero em Core Web Vitals.
- **Rápido e seguro** — API com throttle, token por conta, dados isolados por tenant.
- **Customização múltipla** — widget, segmentação, categorias, banners, tudo configurável.
- Tagline oficial (de `plano-changelog-saas.md`): *"Anuncie novos recursos no seu produto, promova engajamento e mantenha seus usuários atualizados — de maneira simples e sem esforço."*

---

## 2. Estudo de conversão — padrões aplicados

Referências analisadas: Wise (base do DESIGN.md), Linear, Stripe, Beamer, Canny e Headway (concorrentes diretos citados em `plano-changelog-saas.md`).

| Padrão de alta conversão | Como aplicar no Novidda |
|---|---|
| **Headline de benefício em ≤ 8 palavras** (Wise: "Money without borders") | Display 900 no hero: "Anuncie novidades dentro do seu produto." |
| **CTA único e repetido** — um só verbo de ação, cor exclusiva da marca | Botão violet "Criar conta grátis" no nav, hero, meio e fim. Nunca dois CTAs primários competindo. |
| **Prova tangível acima da dobra** (Stripe mostra código; Wise mostra a calculadora) | Mockup animado do widget real ao lado do headline + snippet de instalação de 1 linha com botão copiar. |
| **Redução de fricção explícita** | Sub-headline: "Grátis para sempre. Sem cartão de crédito. Contas e clientes ilimitados." |
| **Ancoragem contra a objeção nº 1** | Concorrentes (Beamer/Canny) cobram por MAU — seção dedicada "Por que gratuito?" e comparativo direto. |
| **Features como benefícios, não como lista técnica** (Linear) | Cada card de feature abre com o resultado ("Fale só com quem interessa") e não com o nome do recurso. |
| **Demonstração de leveza com número concreto** | "Script leve, `async`, zero dependências — seu Core Web Vitals nem percebe." |
| **Momento "aha" reproduzível na página** | Bloco interativo: o próprio widget Novidda embutido na landing (dogfooding) — o visitante clica e vê o produto funcionando. |
| **FAQ para objeções finais + CTA de fechamento** (padrão SaaS universal) | FAQ com schema JSON-LD + banda final dark com CTA. |
| **F-pattern e uma ideia por seção** | Seções curtas, alternância de fundos canvas ↔ canvas-soft ↔ ink para ritmo visual. |

---

## 3. Estrutura da página (seção por seção)

Ordem pensada como funil: atenção → demonstração → features → confiança → objeções → fechamento.

### 3.1 Nav (sticky)
- Fundo `--canvas` com blur ao rolar; logo `Novidda_Logo.png` à esquerda.
- Links âncora: Funcionalidades · Como funciona · Roadmap · FAQ.
- Direita: "Entrar" (tertiary, borda ink) → `route('login')` + "Criar conta grátis" (primary violet) → `route('register')`.

### 3.2 Hero (banda `--canvas-soft` `#E0DCFF`)
- Badge pill: ícone `fa-solid fa-gift` + "100% gratuito · contas ilimitadas".
- H1 display 900 (Inter 900, clamp 40→72px): **"Anuncie novidades dentro do seu produto."**
- Sub (body-lg): "Changelog, roadmap com votação e banners contextuais em um widget leve que não atrapalha a performance. Grátis para sempre, para quantos clientes você quiser."
- CTA primário "Criar conta grátis" + secundário "Ver funcionalidades" (âncora).
- Direita: **mockup animado do widget** (painel flutuante com cards de novidade entrando em cascata, badge de não-lidos pulsando) — construído em HTML/CSS puro, sem imagem pesada.
- Movimento: entrada staggered (fade + translateY) dos elementos; blobs de gradiente violet suaves ao fundo com `animation` lenta.

### 3.3 Barra de confiança (banda `--canvas`)
- 4 mini-stats inline: "1 linha de código" · "0 dependências" · "R$ 0 para sempre" · "∞ contas e clientes".

### 3.4 Como funciona — instalação em 1 linha (banda `--canvas`)
- H2: "No ar em menos de 1 minuto."
- 3 passos numerados: ① Crie sua conta grátis ② Cole 1 linha de código ③ Publique sua primeira novidade.
- Bloco de código real com botão copiar:
  ```html
  <script src="https://novidda.com.br/widget.js" data-token="SEU_TOKEN" async></script>
  ```
- Callout de performance: card com ícone `fa-solid fa-bolt` — "Carregamento assíncrono e sob demanda. Zero impacto no seu Core Web Vitals."

### 3.5 Funcionalidades (banda `--canvas-soft`) — grid 3×N de cards
Cards `rounded 24px`, hover com elevação sutil, entrada com scroll-reveal staggered. Cada card abre com um ícone Font Awesome (`fa-solid`, 20–24px, violet, dentro de um círculo `--primary-pale`). Um card destacado dark (`--ink` com texto violet) para segmentação (feature diferencial):

| Ícone (Font Awesome) | Card | Título (benefício) | Descrição curta |
|---|---|---|---|
| `fa-newspaper` | Feed de novidades | "Publique novidades onde o usuário está" | Editor rico, imagens e vídeos, categorias e rascunhos. |
| `fa-bullseye` | Segmentação **(card dark destaque)** | "Fale só com quem interessa" | Segmente por plano, papel, empresa ou qualquer atributo do seu usuário. |
| `fa-bullhorn` | Banner contextual | "O anúncio certo, na página certa" | Dispare banners por contexto ou evento, com contador regressivo. |
| `fa-map` | Roadmap com votação | "Deixe seus usuários votarem no futuro" | Roadmap público com votos, comentários e feedback. |
| `fa-comments` | Reações e comentários | "Transforme anúncio em conversa" | Reações e comentários com moderação embutida. |
| `fa-chart-line` | Analytics | "Saiba o que engajou" | Visualizações, leituras e reações — por segmento de audiência. |
| `fa-building` | Multi-contas | "Todos os seus clientes em um lugar" | Contas ilimitadas. Perfeito para agências. |
| `fa-palette` | Customização | "Com a cara do seu produto" | Posição, cores, ícone e comportamento do widget. |
| `fa-moon` | Modo escuro | "Bonito em qualquer tema" | Widget e painel com dark mode nativo. |

### 3.6 Demonstração — computador com o widget em uso (banda `--canvas`) [IMPLEMENTADO]
- H2: "O widget, dentro do seu sistema."
- Em vez de embutir o widget real (dependeria de conta demo pública, adiado — ver seção 9), foi construído um mockup 100% HTML/CSS de um monitor de computador exibindo um sistema fictício (sidebar + dashboard com mini-gráfico) com o painel do widget Novidda aberto por cima, ancorado à direita — reproduzindo fielmente a estrutura de `claude/specs/referencias/widget-captura.png`: header "Novidades" + fechar, pills de filtro (Todos/Correção/Novidade), divisor "Anteriores", 3 itens de feed com barra lateral colorida (violet/negativo/cyan) e tags (Anúncio · Hotfix+Correção · Feature+Novidade), reação com coração, vídeo com botão de play, abas Releases/Roadmap e rodapé "Feito por Novidda".
- Sem imagens externas — vídeo é um placeholder em gradiente + triângulo CSS puro (sem hotlink).
- Escala fluida via `font-size` em `clamp(vw)` no container do "monitor", com breakpoint próprio abaixo de 768px (`aspect-ratio` mais alto) para caber o item de vídeo sem cortar, dado que a coluna do widget fica mais estreita em telas menores.

### 3.7 "Por que gratuito?" / comparativo (banda `--canvas`)
- H2: "Gratuito. De verdade."
- Texto curto e honesto + tabela comparativa Novidda × concorrentes (Beamer/Canny cobram por usuário ativo): linhas para preço, limite de contas, limite de clientes, segmentação, roadmap. Células de sim/não com `fa-check` (positive `#2EB87A`) e `fa-xmark` (mute `#8B8BA0`) — nunca emojis de check.
- Reforço: "Sem cartão de crédito. Sem plano pago escondido. Sem limite de posts."

### 3.8 Performance e segurança (banda dark `--ink`, texto claro)
- H2 em violet claro: "Leve por design."
- 3 colunas com ícones Font Awesome em violet claro: **Rápido** `fa-gauge-high` (async, lazy, zero dependências) · **Seguro** `fa-shield-halved` (token por conta, throttle de API, dados isolados por conta) · **Sem rastreadores** `fa-eye-slash` (nenhum script de terceiros no seu site).
- Usar `Novidda_Logo_font_white.png` se a logo aparecer nesta banda.

### 3.9 FAQ (banda `--canvas`)
`<details>/<summary>` estilizados (acessível, sem JS obrigatório) + JSON-LD `FAQPage`:
1. O Novidda é gratuito mesmo? — Sim, 100%, sem limite de contas ou clientes.
2. O widget deixa meu site lento? — Não: script async, zero dependências, conteúdo carregado sob demanda.
3. Posso usar em vários clientes/produtos? — Sim, contas ilimitadas com token próprio.
4. Preciso de cartão de crédito? — Não.
5. Como instalo? — 1 linha de código; com identificação de usuário para segmentação, 3 linhas.
6. Funciona com qualquer stack? — Sim, é um `<script>` — funciona em qualquer site ou web app.

### 3.10 CTA final (banda `--canvas-soft` com blob violet)
- H2 display 900: "Comece a anunciar hoje. De graça."
- CTA "Criar conta grátis" grande + micro-copy "Leva menos de 1 minuto".

### 3.11 Footer (banda `--ink`)
- Logo branca, tagline, links (Funcionalidades, Login, Criar conta, Documentação de instalação), © Novidda 2026.

---

## 4. Design — tokens obrigatórios (de `DESIGN.md`)

- **Primária/CTA:** `#7B61FF` (Signal Violet) — cor exclusiva de CTA, nunca reutilizar como sucesso. Hover `#9D8FFF`, pale `#E8E4FF`.
- **Superfícies:** canvas `#F5F5F0` (off-white quente) ↔ canvas-soft `#E0DCFF` ↔ ink `#16213E` (bandas dark). Contraste de superfície = elevação (sem sombras pesadas).
- **Texto:** ink `#16213E`, body `#3D3D5C`, mute `#8B8BA0`.
- **Tipografia:** Inter carregada via Google Fonts (`wght@400;600;900`) com `preconnect` + `display=swap`. Display weight **900** para hero/H2 de seção; 600 para subtítulos; 400 corpo. Escala fluida com `clamp()`.
- **Raio:** 24px em cards e botões (assinatura da marca), pill 9999px em badges. Nunca cantos retos em CTA.
- **Botões:** primary = violet + texto `#F5F5F0`; tertiary = canvas + borda 1px ink. Altura ≥ 48px (touch target).

## 5. Movimento (moderno, minimalista, performático)

- **Scroll-reveal** com `IntersectionObserver` (classe `.reveal` → `opacity/transform` com `transition`), stagger de 60–80ms entre cards. ~30 linhas de JS vanilla inline, sem biblioteca.
- **Hero:** entrada em cascata dos elementos ao carregar; mockup do widget com cards animados em loop lento (keyframes CSS); badge de não-lidos com pulse.
- **Fundo:** 2 blobs radiais violet com `animation: float 18s ease-in-out infinite alternate` (apenas `transform`, compositor-friendly).
- **Micro-interações:** hover de card (translateY -4px), hover de CTA (escurece + leve scale), nav ganha sombra/blur após 24px de scroll.
- **Acessibilidade:** todo movimento dentro de `@media (prefers-reduced-motion: reduce) { … }` desativado.
- **Proibido:** parallax pesado, bibliotecas de animação, vídeos de fundo — a landing precisa provar a tese de leveza que ela mesma vende.

## 6. SEO — checklist completo

- **`<html lang="pt-BR">`**, charset UTF-8, viewport.
- **Title (≤60 chars):** `Novidda — Changelog e anúncio de novidades in-app, 100% grátis`.
- **Meta description (≤155 chars):** `Anuncie novidades dentro do seu produto com widget leve de changelog, roadmap com votação e banners. 100% gratuito, contas ilimitadas, sem cartão.`
- **Canonical:** `https://novidda.com.br/`.
- **Open Graph + Twitter Card:** `og:title`, `og:description`, `og:image` (criar `public/img/og-novidda.png` 1200×630 — pode ser gerado a partir do hero), `og:locale pt_BR`, `twitter:card summary_large_image`.
- **JSON-LD:** `SoftwareApplication` (name, applicationCategory `BusinessApplication`, `offers { price: 0, priceCurrency: BRL }`, description) + `FAQPage` (perguntas da seção 3.9) + `Organization` com logo.
- **HTML semântico:** um único `<h1>`, hierarquia H2/H3 correta, `<header>/<main>/<section>/<footer>`, `alt` em todas as imagens, âncoras descritivas.
- **Performance (CWV é fator de ranking):** CSS crítico inline ou arquivo único pequeno (`public/css/landing.css`), zero JS bloqueante (script inline `defer`-equivalente no fim do body), logo PNG com `width/height` explícitos, `loading="lazy"` abaixo da dobra, `preconnect` para fonts. Meta: Lighthouse ≥ 95 em Performance/SEO/Accessibility/Best Practices.
- **`public/robots.txt`:** revisar — permitir `/`, bloquear `/dashboard`, `/login`, `/register` de indexação desnecessária? (manter `/` e páginas públicas liberadas).
- **Sitemap:** criar `public/sitemap.xml` simples com a home (fase 2: rota dinâmica se surgirem mais páginas públicas).
- **Keywords alvo (pt-BR):** "changelog para saas", "anunciar novidades no sistema", "widget de novidades", "changelog grátis", "roadmap público com votação", "product announcement in-app". Distribuir naturalmente em headings e corpo.

## 7. Implementação técnica

### Arquivos a criar/alterar

| Arquivo | Ação |
|---|---|
| `routes/web.php:23` | Rota `/` sempre exibe `view('landing')` (sem redirect para login/dashboard, mesmo autenticado). Rota nomeada `home`. |
| `resources/views/landing.blade.php` | View única e autocontida da landing (Blade puro, sem Vite). Usa `route('login')`/`route('register')` e `asset()` para imagens. |
| `public/css/landing.css` | CSS próprio da landing (não poluir `neu.css`). Variáveis copiadas dos tokens do DESIGN.md. Mobile-first, breakpoints 768/1024px. |
| `public/img/og-novidda.png` | Imagem OG 1200×630 (fase 1 pode usar a logo sobre fundo canvas-soft). |
| `public/sitemap.xml` | Sitemap estático com a home. |
| `public/robots.txt` | Revisar/adicionar referência ao sitemap. |

### Decisões técnicas

- **PHP/Blade puro, sem build step:** a landing não passa pelo Vite — CSS e JS mínimos servidos direto de `public/`. Deploy simples via FTP (workflow existente).
- **Ícones (regra sem emojis):** usar o Font Awesome local (`public/vendor/fontawesome/`). Como o CSS completo do FA é pesado para uma landing que vende leveza, preferir uma destas opções, nesta ordem: (a) copiar os SVGs dos ~15 ícones usados como SVG inline no Blade (custo zero de request); (b) gerar um subset CSS só com os ícones usados; (c) em último caso, carregar o `fontawesome.min.css` local com `media="print" onload` para não bloquear render. Nunca CDN externo.
- **JS da landing inline no Blade** (~40 linhas: scroll-reveal, nav sticky, botão copiar snippet com fallback de clipboard — reaproveitar o fix do commit `b4743d3`).
- **Responsivo:** hero empilha < 768px; grid de features 1→2→3 colunas; nav mobile com CTA visível (menu hambúrguer só se necessário).
- **Dark mode:** fase 1 entrega apenas tema claro (padrão de landing); estrutura de variáveis CSS preparada para `prefers-color-scheme` em fase 2.
- **Sem dependência de sessão/DB no render** da landing — página essencialmente estática, cacheável.

### Ordem de execução (checklist)

1. [x] `public/css/landing.css` com tokens + primitivas (botões, cards, bandas, tipografia fluida).
2. [x] `resources/views/landing.blade.php` — estrutura semântica completa das seções com copy final (3.6 demo ao vivo ficou no fallback de fase 2, conforme previsto).
3. [x] Mockup animado do widget em HTML/CSS no hero.
4. [x] JS inline: scroll-reveal + nav + copiar snippet (com fallback de clipboard).
5. [x] Rota `/` em `routes/web.php` (mantendo redirect de autenticado para dashboard; rota nomeada `home`).
6. [x] SEO: metas, OG, JSON-LD (SoftwareApplication + FAQPage + Organization) — validados via parse —, sitemap, robots.
7. [x] Imagem OG gerada com PHP GD (`public/img/og-novidda.png`, 1200×630).
8. [x] Teste responsivo via Chrome headless (600/1440px; nota: headless tem largura mínima de janela ~500px, 375px real verificado por análise do CSS mobile-first) + `prefers-reduced-motion` implementado.
9. [x] Verificação: página renderiza HTTP 200, um único `<h1>`, zero emojis no HTML, CTAs levam a `/login` e `/register`.
10. [ ] Lighthouse ≥ 95 nas 4 categorias (medir em ambiente com Lighthouse disponível).

## 8. Critérios de aceite

- `http://127.0.0.1:8000/` (e futuramente `http://novidda.com.br/`) sempre exibe a landing — nunca redireciona para `/login` ou `/dashboard`.
- Copy sem promessas absolutas de gratuidade eterna ("para sempre", "sem pegadinha", "sem plano pago escondido") — a plataforma terá opção de pagamento no futuro; usar termos como "plano gratuito completo".
- Botões "Entrar" e "Criar conta grátis" levam a `/login` e `/register`.
- Todas as 10 funcionalidades do inventário aparecem na página.
- As 4 mensagens-chave (gratuito/ilimitado, performance, rápido/seguro, customização) aparecem no hero ou na primeira dobra e meia.
- Logos oficiais (`Novidda_Logo.png` / `_font_white.png`) usadas; paleta e raios seguem o DESIGN.md.
- **Zero emojis no HTML renderizado**; toda iconografia via Font Awesome (SVG inline ou subset local), com `aria-hidden="true"`.
- Animações suaves presentes e desativadas sob `prefers-reduced-motion`.
- HTML válido, um `<h1>`, JSON-LD válido (testar no Rich Results Test), Lighthouse ≥ 95.

## 9. Fora de escopo (fase 2)

- Conta demo pública para o dogfooding do widget na landing (seção 3.6).
- Dark mode da landing.
- Páginas adicionais (blog, docs públicas, página de comparação por concorrente para SEO programático).
- Sitemap dinâmico e hreflang (se houver versão em inglês no futuro).
