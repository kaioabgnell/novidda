<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Novidda — Changelog e anúncio de novidades in-app, 100% grátis</title>
    <meta name="description" content="Anuncie novidades dentro do seu produto com widget leve de changelog, roadmap com votação e banners. 100% gratuito, contas ilimitadas, sem cartão.">
    <link rel="canonical" href="https://novidda.com.br/">
    <meta name="robots" content="index, follow">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Novidda">
    <meta property="og:title" content="Novidda — Anuncie novidades dentro do seu produto">
    <meta property="og:description" content="Changelog, roadmap com votação e banners contextuais em um widget leve. 100% gratuito, contas e clientes ilimitados.">
    <meta property="og:url" content="https://novidda.com.br/">
    <meta property="og:image" content="https://novidda.com.br/img/og-novidda.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="pt_BR">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Novidda — Anuncie novidades dentro do seu produto">
    <meta name="twitter:description" content="Changelog, roadmap com votação e banners contextuais em um widget leve. 100% gratuito, contas e clientes ilimitados.">
    <meta name="twitter:image" content="https://novidda.com.br/img/og-novidda.png">

    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/landing.css') }}?v=1">

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Novidda",
        "url": "https://novidda.com.br/",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "inLanguage": "pt-BR",
        "description": "Plataforma gratuita de anúncio de novidades in-app: changelog com widget leve, segmentação de audiência, banners contextuais, roadmap com votação e analytics de engajamento.",
        "offers": { "@type": "Offer", "price": "0", "priceCurrency": "BRL" }
    }
    </script>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Novidda",
        "url": "https://novidda.com.br/",
        "logo": "https://novidda.com.br/img/Novidda_Logo.png"
    }
    </script>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "O Novidda é gratuito mesmo?",
                "acceptedAnswer": { "@type": "Answer", "text": "Sim, 100% gratuito, sem limite de contas ou de clientes — todas as funcionalidades estão incluídas." }
            },
            {
                "@type": "Question",
                "name": "O widget deixa meu site lento?",
                "acceptedAnswer": { "@type": "Answer", "text": "Não. O script carrega de forma assíncrona, tem zero dependências e o conteúdo só é buscado sob demanda — impacto zero no seu Core Web Vitals." }
            },
            {
                "@type": "Question",
                "name": "Posso usar em vários clientes ou produtos?",
                "acceptedAnswer": { "@type": "Answer", "text": "Sim. Crie quantas contas quiser, cada uma com seu próprio token e configuração — ideal para agências e quem atende vários clientes." }
            },
            {
                "@type": "Question",
                "name": "Preciso de cartão de crédito?",
                "acceptedAnswer": { "@type": "Answer", "text": "Não. Basta criar a conta e começar a publicar." }
            },
            {
                "@type": "Question",
                "name": "Como instalo o widget?",
                "acceptedAnswer": { "@type": "Answer", "text": "Colando uma linha de código no seu site. Para usar segmentação por usuário, são três linhas: um objeto de configuração com os atributos do usuário e o script." }
            },
            {
                "@type": "Question",
                "name": "Funciona com qualquer stack?",
                "acceptedAnswer": { "@type": "Answer", "text": "Sim. É um script simples, funciona em qualquer site ou aplicação web, independentemente da tecnologia." }
            }
        ]
    }
    </script>
</head>
<body>

{{-- Sprite de ícones (SVG inline, traço estilo Font Awesome/Lucide — regra: sem emojis) --}}
<svg xmlns="http://www.w3.org/2000/svg" style="display:none" aria-hidden="true">
    <symbol id="i-gift" viewBox="0 0 24 24"><rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13"/><path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"/><path d="M7.5 8a2.5 2.5 0 0 1 0-5C11 3 12 8 12 8s1-5 4.5-5a2.5 2.5 0 0 1 0 5"/></symbol>
    <symbol id="i-bolt" viewBox="0 0 24 24"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></symbol>
    <symbol id="i-newspaper" viewBox="0 0 24 24"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></symbol>
    <symbol id="i-target" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></symbol>
    <symbol id="i-megaphone" viewBox="0 0 24 24"><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></symbol>
    <symbol id="i-map" viewBox="0 0 24 24"><path d="M14.1 5.6a2 2 0 0 0 1.8 0l3.7-1.9A1 1 0 0 1 21 4.6v12.8a1 1 0 0 1-.6.9l-4.5 2.3a2 2 0 0 1-1.8 0l-4.2-2.1a2 2 0 0 0-1.8 0l-3.7 1.9A1 1 0 0 1 3 19.4V6.6a1 1 0 0 1 .6-.9l4.5-2.3a2 2 0 0 1 1.8 0l4.2 2.2z"/><path d="M15 5.8v15"/><path d="M9 3.2v15"/></symbol>
    <symbol id="i-comments" viewBox="0 0 24 24"><path d="M14 9a2 2 0 0 1-2 2H6l-4 4V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2z"/><path d="M18 9h2a2 2 0 0 1 2 2v11l-4-4h-6a2 2 0 0 1-2-2v-1"/></symbol>
    <symbol id="i-chart" viewBox="0 0 24 24"><path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="m19 9-5 5-4-4-3 3"/></symbol>
    <symbol id="i-building" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01M12 6h.01M16 6h.01M8 10h.01M12 10h.01M16 10h.01M8 14h.01M12 14h.01M16 14h.01"/></symbol>
    <symbol id="i-palette" viewBox="0 0 24 24"><circle cx="13.5" cy="6.5" r=".5"/><circle cx="17.5" cy="10.5" r=".5"/><circle cx="8.5" cy="7.5" r=".5"/><circle cx="6.5" cy="12.5" r=".5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.9 0 1.6-.7 1.6-1.7 0-.4-.2-.8-.4-1.1-.3-.3-.4-.7-.4-1.1a1.6 1.6 0 0 1 1.7-1.7h2c3 0 5.5-2.5 5.5-5.6C22 6 17.5 2 12 2z"/></symbol>
    <symbol id="i-moon" viewBox="0 0 24 24"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></symbol>
    <symbol id="i-heart" viewBox="0 0 24 24"><path d="M19 14c1.5-1.5 2-3 2-4.5A4.5 4.5 0 0 0 16.5 5c-1.8 0-3.4 1-4.5 2.5C10.9 6 9.3 5 7.5 5A4.5 4.5 0 0 0 3 9.5c0 1.5.5 3 2 4.5l7 7z"/></symbol>
    <symbol id="i-gauge" viewBox="0 0 24 24"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></symbol>
    <symbol id="i-shield" viewBox="0 0 24 24"><path d="M20 13c0 5-3.5 7.5-7.7 9a1 1 0 0 1-.6 0C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.2-2.7a1 1 0 0 1 1.6 0C14.5 3.8 17 5 19 5a1 1 0 0 1 1 1z"/></symbol>
    <symbol id="i-eye-off" viewBox="0 0 24 24"><path d="M10.7 5.1A10.7 10.7 0 0 1 21.9 11.6a1 1 0 0 1 0 .7 10.7 10.7 0 0 1-1.4 2.5"/><path d="M14.1 14.2a3 3 0 0 1-4.2-4.2"/><path d="M17.5 17.5a10.8 10.8 0 0 1-15.4-5.2 1 1 0 0 1 0-.7 10.8 10.8 0 0 1 4.4-5.1"/><path d="m2 2 20 20"/></symbol>
    <symbol id="i-check" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></symbol>
    <symbol id="i-x" viewBox="0 0 24 24"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></symbol>
    <symbol id="i-copy" viewBox="0 0 24 24"><rect x="8" y="8" width="14" height="14" rx="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></symbol>
    <symbol id="i-chevron" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></symbol>
    <symbol id="i-bell" viewBox="0 0 24 24"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.9 1.9 0 0 0 3.4 0"/></symbol>
    <symbol id="i-thumbs-up" viewBox="0 0 24 24"><path d="M7 10v12"/><path d="M15 5.9 14 10h5.8a2 2 0 0 1 1.9 2.6l-2.3 7a2 2 0 0 1-1.9 1.4H4a1 1 0 0 1-1-1v-9a1 1 0 0 1 1-1h2.8a2 2 0 0 0 1.6-.8l4.3-5.8a1.7 1.7 0 0 1 3 1.5z"/></symbol>
    <symbol id="i-star" viewBox="0 0 24 24"><path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.9L12 17.8 5.8 21l1.2-6.9-5-4.9 6.9-1z"/></symbol>
    <symbol id="i-wrench" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></symbol>
    <symbol id="i-image" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-4.3-4.3a2 2 0 0 0-2.8 0L6 19"/></symbol>
</svg>

{{-- ============ 3.1 Nav ============ --}}
<header class="nav" id="nav">
    <div class="container nav-inner">
        <a class="nav-logo" href="{{ route('home') }}" aria-label="Novidda — página inicial">
            <img src="{{ asset('img/Novidda_Logo.png') }}" alt="Novidda" width="132" height="33">
        </a>
        <nav aria-label="Navegação principal">
            <ul class="nav-links">
                <li><a href="#funcionalidades">Funcionalidades</a></li>
                <li><a href="#como-funciona">Como funciona</a></li>
                <li><a href="#demo">Veja funcionando</a></li>
                <li><a href="#gratuito">Por que gratuito</a></li>
                <li><a href="#faq">FAQ</a></li>
            </ul>
        </nav>
        <div class="nav-actions">
            <a class="btn btn-ghost" href="{{ route('login') }}">Entrar</a>
            <a class="btn btn-primary" href="{{ route('register') }}">Criar conta grátis</a>
        </div>
    </div>
</header>

<main>

    {{-- ============ 3.2 Hero ============ --}}
    <section class="hero">
        <div class="blob blob-a" aria-hidden="true"></div>
        <div class="blob blob-b" aria-hidden="true"></div>
        <div class="container hero-inner">
            <div class="hero-stagger">
                <span class="hero-badge">
                    <svg class="icon" aria-hidden="true"><use href="#i-gift"/></svg>
                    100% gratuito &middot; contas ilimitadas
                </span>
                <h1>
                    <span class="sr-only">Anuncie novidades, melhorias, hotfixes e roadmap dentro do seu produto.</span>
                    <span aria-hidden="true">Anuncie<br><span class="typewriter-wrap"><span class="typewriter" id="hero-typewriter">novidades</span><span class="typewriter-cursor"></span></span> dentro<br>do seu produto.</span>
                </h1>
                <p class="hero-sub">Changelog, roadmap com votação e banners contextuais em um widget leve que não atrapalha a performance. Gratuito, para quantos clientes você quiser.</p>
                <div class="hero-ctas">
                    <a class="btn btn-primary btn-lg" href="{{ route('register') }}">Criar conta grátis</a>
                    <a class="btn btn-tertiary btn-lg" href="#funcionalidades">Ver funcionalidades</a>
                </div>
                <p class="hero-note">Sem cartão de crédito. No ar em menos de 1 minuto.</p>
            </div>

            {{-- Mockup animado do widget (HTML/CSS puro) --}}
            <div class="hero-visual" aria-hidden="true">
                <div class="mock-panel">
                    <div class="mock-head">
                        <strong>Novidades</strong>
                        <span class="mock-pill">3 novas</span>
                    </div>
                    <div class="mock-card">
                        <span class="mock-tag mock-tag-new">Novidade</span>
                        <div class="mock-title"></div>
                        <div class="mock-line"></div>
                        <div class="mock-line short"></div>
                        <div class="mock-reactions">
                            <span><svg class="icon"><use href="#i-heart"/></svg></span>
                            <span><svg class="icon"><use href="#i-thumbs-up"/></svg></span>
                            <span><svg class="icon"><use href="#i-star"/></svg></span>
                        </div>
                    </div>
                    <div class="mock-card">
                        <span class="mock-tag mock-tag-improve">Melhoria</span>
                        <div class="mock-title" style="width:58%"></div>
                        <div class="mock-line"></div>
                        <div class="mock-line short"></div>
                    </div>
                    <div class="mock-card">
                        <span class="mock-tag mock-tag-fix">Correção</span>
                        <div class="mock-title" style="width:66%"></div>
                        <div class="mock-line short"></div>
                    </div>
                    <button class="mock-launcher" type="button" tabindex="-1">
                        <svg class="icon"><use href="#i-bell"/></svg>
                        <span class="mock-badge">3</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ 3.3 Barra de confiança ============ --}}
    <section class="stats" aria-label="Números do Novidda">
        <div class="container stats-grid">
            <div class="stat reveal"><strong>1</strong><span>linha de código para instalar</span></div>
            <div class="stat reveal" style="--reveal-delay:.07s"><strong>0</strong><span>dependências no seu site</span></div>
            <div class="stat reveal" style="--reveal-delay:.14s"><strong>R$ 0</strong><span>no plano gratuito completo</span></div>
            <div class="stat reveal" style="--reveal-delay:.21s"><strong>&infin;</strong><span>contas e clientes</span></div>
        </div>
    </section>

    {{-- ============ 3.4 Como funciona ============ --}}
    <section class="section" id="como-funciona">
        <div class="container">
            <div class="section-head reveal">
                <span class="eyebrow">Como funciona</span>
                <h2>No ar em menos de 1 minuto.</h2>
                <p>Crie a conta, cole uma linha de código e publique sua primeira novidade. É só isso.</p>
            </div>
            <div class="steps">
                <div class="step reveal">
                    <span class="step-num" aria-hidden="true">1</span>
                    <h3>Crie sua conta grátis</h3>
                    <p>Cadastro simples, sem cartão de crédito. Cada conta ganha um token próprio.</p>
                </div>
                <div class="step reveal" style="--reveal-delay:.08s">
                    <span class="step-num" aria-hidden="true">2</span>
                    <h3>Cole 1 linha de código</h3>
                    <p>Adicione o script no seu site ou aplicação — funciona com qualquer stack.</p>
                </div>
                <div class="step reveal" style="--reveal-delay:.16s">
                    <span class="step-num" aria-hidden="true">3</span>
                    <h3>Publique sua primeira novidade</h3>
                    <p>Escreva no editor, escolha a audiência e publique. Seus usuários veem na hora.</p>
                </div>
            </div>
            <div class="code-wrap reveal">
                <div class="code-block">
                    <code id="install-snippet">&lt;script src="https://novidda.com.br/widget.js" <span class="tk">data-token</span>=<span class="tv">"SEU_TOKEN"</span> <span class="tk">async</span>&gt;&lt;/script&gt;</code>
                    <button class="copy-btn" type="button" id="copy-snippet" aria-label="Copiar código de instalação">
                        <svg class="icon" aria-hidden="true"><use href="#i-copy"/></svg>
                    </button>
                </div>
                <div class="perf-callout">
                    <svg class="icon" aria-hidden="true"><use href="#i-bolt"/></svg>
                    <p><strong>Carregamento assíncrono e sob demanda.</strong> O widget não bloqueia o render da página e só busca o conteúdo quando o usuário interage. Zero impacto no seu Core Web Vitals.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ 3.5 Funcionalidades ============ --}}
    <section class="section section-soft" id="funcionalidades">
        <div class="container">
            <div class="section-head reveal">
                <span class="eyebrow">Funcionalidades</span>
                <h2>Tudo para anunciar novidades no seu sistema.</h2>
                <p>Do changelog ao roadmap com votação, com múltiplas opções de customização — sem pagar nada por isso.</p>
            </div>
            <div class="features-grid">
                <article class="feature reveal">
                    <span class="feature-icon"><svg class="icon" aria-hidden="true"><use href="#i-newspaper"/></svg></span>
                    <h3>Publique novidades onde o usuário está</h3>
                    <p>Feed de novidades com editor rico, categorias, rascunhos e controle de leitura por usuário.</p>
                </article>
                <article class="feature feature-dark reveal" style="--reveal-delay:.06s">
                    <span class="feature-icon"><svg class="icon" aria-hidden="true"><use href="#i-target"/></svg></span>
                    <h3>Fale só com quem interessa</h3>
                    <p>Segmente por empresa, usuário, plano ou qualquer atributo customizado — e veja a estimativa de alcance antes de publicar.</p>
                </article>
                <article class="feature reveal" style="--reveal-delay:.12s">
                    <span class="feature-icon"><svg class="icon" aria-hidden="true"><use href="#i-megaphone"/></svg></span>
                    <h3>O anúncio certo, na página certa</h3>
                    <p>Banners contextuais em páginas específicas do seu sistema, com contador regressivo ao vivo para lançamentos e prazos.</p>
                </article>
                <article class="feature reveal">
                    <span class="feature-icon"><svg class="icon" aria-hidden="true"><use href="#i-map"/></svg></span>
                    <h3>Deixe seus usuários votarem no futuro</h3>
                    <p>Menu de roadmap público com votação, comentários e coleta de feedback direto no widget.</p>
                </article>
                <article class="feature reveal" style="--reveal-delay:.06s">
                    <span class="feature-icon"><svg class="icon" aria-hidden="true"><use href="#i-image"/></svg></span>
                    <h3>Imagens e vídeos no seu anúncio</h3>
                    <p>Administre imagens e vídeos do YouTube incorporados em cada novidade, direto no editor.</p>
                </article>
                <article class="feature reveal" style="--reveal-delay:.12s">
                    <span class="feature-icon"><svg class="icon" aria-hidden="true"><use href="#i-comments"/></svg></span>
                    <h3>Transforme anúncio em conversa</h3>
                    <p>Reações e comentários em cada novidade, com fila de moderação para aprovar ou rejeitar antes de publicar.</p>
                </article>
                <article class="feature reveal">
                    <span class="feature-icon"><svg class="icon" aria-hidden="true"><use href="#i-chart"/></svg></span>
                    <h3>Saiba o que engajou</h3>
                    <p>Analytics de visualizações, leituras e reações — segmentado pela audiência elegível de cada anúncio.</p>
                </article>
                <article class="feature reveal" style="--reveal-delay:.06s">
                    <span class="feature-icon"><svg class="icon" aria-hidden="true"><use href="#i-building"/></svg></span>
                    <h3>Todos os seus clientes em um lugar</h3>
                    <p>Contas ilimitadas, cada uma com token e configuração próprios. Perfeito para agências e consultorias.</p>
                </article>
                <article class="feature reveal" style="--reveal-delay:.12s">
                    <span class="feature-icon"><svg class="icon" aria-hidden="true"><use href="#i-palette"/></svg></span>
                    <h3>Com a cara do seu produto</h3>
                    <p>Personalize posição, cores, ícone e aplique CSS customizado para o widget nascer com a cara da sua marca.</p>
                </article>
                <article class="feature reveal">
                    <span class="feature-icon"><svg class="icon" aria-hidden="true"><use href="#i-moon"/></svg></span>
                    <h3>Bonito em qualquer tema</h3>
                    <p>Widget e painel com modo escuro nativo — a experiência acompanha o tema do seu sistema.</p>
                </article>
            </div>
        </div>
    </section>

    {{-- ============ 3.6 Demonstração — widget dentro do sistema ============ --}}
    <section class="section" id="demo">
        <div class="container">
            <div class="section-head reveal">
                <span class="eyebrow">Veja funcionando</span>
                <h2>O widget, dentro do seu sistema.</h2>
                <p>É assim que seus usuários veem as novidades — sem sair do produto, sem recarregar a página.</p>
            </div>
            <div class="device reveal">
                <div class="device-monitor">
                    <span class="device-cam" aria-hidden="true"></span>
                    <div class="device-screen" aria-hidden="true">
                        <div class="fakeapp">
                            <aside class="fakeapp-side">
                                <span class="fakeapp-dot"></span>
                                <span class="fakeapp-nav active"></span>
                                <span class="fakeapp-nav"></span>
                                <span class="fakeapp-nav"></span>
                                <span class="fakeapp-nav"></span>
                                <span class="fakeapp-nav"></span>
                            </aside>
                            <div class="fakeapp-main">
                                <div class="fakeapp-top">
                                    <span class="fakeapp-search"></span>
                                    <span class="fakeapp-avatar"></span>
                                </div>
                                <div class="fakeapp-cards">
                                    <div class="fakeapp-card">
                                        <span class="fakeapp-num">128%</span>
                                        <span class="fakeapp-bar" style="--h:70%"></span>
                                    </div>
                                    <div class="fakeapp-card">
                                        <span class="fakeapp-num">92%</span>
                                        <span class="fakeapp-bar" style="--h:45%"></span>
                                    </div>
                                </div>
                                <div class="fakeapp-chart">
                                    <span style="--h:55%"></span>
                                    <span style="--h:75%"></span>
                                    <span style="--h:40%"></span>
                                    <span style="--h:90%"></span>
                                    <span style="--h:60%"></span>
                                    <span style="--h:100%"></span>
                                </div>
                            </div>
                        </div>

                        <div class="wf">
                            <div class="wf-head">
                                <strong>Novidades</strong>
                                <svg class="icon" aria-hidden="true"><use href="#i-x"/></svg>
                            </div>
                            <div class="wf-filters">
                                <span class="wf-filter active">Todos</span>
                                <span class="wf-filter"><svg class="icon" aria-hidden="true"><use href="#i-wrench"/></svg>Correção</span>
                                <span class="wf-filter"><svg class="icon" aria-hidden="true"><use href="#i-star"/></svg>Novidade</span>
                            </div>
                            <div class="wf-divider"><span>Anteriores</span></div>
                            <div class="wf-feed">
                                <article class="wf-item" style="--bar:var(--primary)">
                                    <div class="wf-item-top">
                                        <span class="wf-tag wf-tag-announce">Anúncio</span>
                                        <time>2 dias atrás</time>
                                    </div>
                                    <h4>Novo painel de métricas em tempo real</h4>
                                    <p>Acompanhe indicadores-chave direto do seu painel.</p>
                                </article>
                                <article class="wf-item" style="--bar:#D03060">
                                    <div class="wf-item-top">
                                        <span class="wf-tag wf-tag-hotfix">Hotfix</span>
                                        <span class="wf-tag wf-tag-outline"><svg class="icon" aria-hidden="true"><use href="#i-wrench"/></svg>Correção</span>
                                        <time>7 dias atrás</time>
                                    </div>
                                    <h4>Correção na exportação de relatórios</h4>
                                    <p>Corrigimos um erro que impedia a exportação em PDF.</p>
                                    <div class="wf-reaction"><svg class="icon" aria-hidden="true"><use href="#i-heart"/></svg>1</div>
                                </article>
                                <article class="wf-item" style="--bar:var(--accent-cyan)">
                                    <div class="wf-item-top">
                                        <span class="wf-tag wf-tag-feature">Feature</span>
                                        <span class="wf-tag wf-tag-outline-primary"><svg class="icon" aria-hidden="true"><use href="#i-star"/></svg>Novidade</span>
                                        <time>7 dias atrás</time>
                                    </div>
                                    <h4>Novo tema escuro</h4>
                                    <p>Lançamos o modo escuro para uma experiência mais confortável.</p>
                                    <div class="wf-video">
                                        <button class="wf-play" type="button" tabindex="-1"><span class="wf-play-triangle"></span></button>
                                    </div>
                                </article>
                            </div>
                            <div class="wf-tabs">
                                <span class="active">Releases</span>
                                <span>Roadmap</span>
                            </div>
                            <div class="wf-footer">Feito por <strong>Novidda</strong></div>
                        </div>
                    </div>
                </div>
                <div class="device-neck" aria-hidden="true"></div>
                <div class="device-base" aria-hidden="true"></div>
            </div>
        </div>
    </section>

    {{-- ============ 3.7 Por que gratuito / comparativo ============ --}}
    <section class="section" id="gratuito">
        <div class="container">
            <div class="section-head reveal">
                <span class="eyebrow">Preço</span>
                <h2>Gratuito. De verdade.</h2>
                <p>As ferramentas de changelog cobram assinaturas que crescem com o número de usuários ativos. O Novidda é 100% gratuito: sem limite de contas, de clientes ou de publicações.</p>
            </div>
            <div class="compare-wrap reveal">
                <table class="compare">
                    <thead>
                        <tr>
                            <th scope="col">O que você leva</th>
                            <th scope="col">Novidda</th>
                            <th scope="col">Ferramentas pagas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row">Preço</th>
                            <td class="yes"><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg>R$ 0, com tudo incluído</td>
                            <td class="no"><svg class="icon" aria-hidden="true"><use href="#i-x"/></svg>Assinatura que cresce com seus usuários</td>
                        </tr>
                        <tr>
                            <th scope="row">Contas e projetos</th>
                            <td class="yes"><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg>Ilimitados</td>
                            <td class="no"><svg class="icon" aria-hidden="true"><use href="#i-x"/></svg>Limitados por plano</td>
                        </tr>
                        <tr>
                            <th scope="row">Clientes atendidos</th>
                            <td class="yes"><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg>Quantos você quiser</td>
                            <td class="no"><svg class="icon" aria-hidden="true"><use href="#i-x"/></svg>Cobrança por projeto ou workspace</td>
                        </tr>
                        <tr>
                            <th scope="row">Segmentação de audiência</th>
                            <td class="yes"><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg>Incluída</td>
                            <td class="no"><svg class="icon" aria-hidden="true"><use href="#i-x"/></svg>Recurso de plano avançado</td>
                        </tr>
                        <tr>
                            <th scope="row">Roadmap com votação</th>
                            <td class="yes"><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg>Incluído</td>
                            <td class="no"><svg class="icon" aria-hidden="true"><use href="#i-x"/></svg>Recurso de plano avançado</td>
                        </tr>
                        <tr>
                            <th scope="row">Cartão de crédito</th>
                            <td class="yes"><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg>Não precisa</td>
                            <td class="no"><svg class="icon" aria-hidden="true"><use href="#i-x"/></svg>Necessário para os recursos completos</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="compare-note reveal">Sem cartão de crédito. Sem limite de posts. Sem limite de contas.</p>
        </div>
    </section>

    {{-- ============ 3.8 Performance e segurança ============ --}}
    <section class="section section-dark" id="performance">
        <div class="container">
            <div class="section-head reveal">
                <span class="eyebrow">Performance e segurança</span>
                <h2>Leve por design.</h2>
                <p>A landing que você está lendo segue a mesma regra do widget: rápido, seguro e sem excessos.</p>
            </div>
            <div class="pillars">
                <div class="pillar reveal">
                    <span class="feature-icon"><svg class="icon" aria-hidden="true"><use href="#i-gauge"/></svg></span>
                    <h3>Rápido</h3>
                    <p>Script assíncrono com zero dependências. O feed só é carregado quando o usuário interage — seu sistema continua rápido como sempre.</p>
                </div>
                <div class="pillar reveal" style="--reveal-delay:.08s">
                    <span class="feature-icon"><svg class="icon" aria-hidden="true"><use href="#i-shield"/></svg></span>
                    <h3>Seguro</h3>
                    <p>Token exclusivo por conta, limite de requisições na API pública e dados totalmente isolados entre contas.</p>
                </div>
                <div class="pillar reveal" style="--reveal-delay:.16s">
                    <span class="feature-icon"><svg class="icon" aria-hidden="true"><use href="#i-eye-off"/></svg></span>
                    <h3>Sem rastreadores</h3>
                    <p>Nenhum script de terceiros entra no seu site. O widget conversa apenas com o Novidda, mais nada.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ 3.9 FAQ ============ --}}
    <section class="section" id="faq">
        <div class="container">
            <div class="section-head reveal">
                <span class="eyebrow">FAQ</span>
                <h2>Perguntas frequentes</h2>
            </div>
            <div class="faq-list">
                <details class="faq-item reveal">
                    <summary>O Novidda é gratuito mesmo? <svg class="icon" aria-hidden="true"><use href="#i-chevron"/></svg></summary>
                    <p>Sim, 100% gratuito, sem limite de contas ou de clientes — todas as funcionalidades estão incluídas.</p>
                </details>
                <details class="faq-item reveal">
                    <summary>O widget deixa meu site lento? <svg class="icon" aria-hidden="true"><use href="#i-chevron"/></svg></summary>
                    <p>Não. O script carrega de forma assíncrona, tem zero dependências e o conteúdo só é buscado sob demanda — impacto zero no seu Core Web Vitals.</p>
                </details>
                <details class="faq-item reveal">
                    <summary>Posso usar em vários clientes ou produtos? <svg class="icon" aria-hidden="true"><use href="#i-chevron"/></svg></summary>
                    <p>Sim. Crie quantas contas quiser, cada uma com seu próprio token e configuração — ideal para agências e quem atende vários clientes.</p>
                </details>
                <details class="faq-item reveal">
                    <summary>Preciso de cartão de crédito? <svg class="icon" aria-hidden="true"><use href="#i-chevron"/></svg></summary>
                    <p>Não. Basta criar a conta e começar a publicar.</p>
                </details>
                <details class="faq-item reveal">
                    <summary>Como instalo o widget? <svg class="icon" aria-hidden="true"><use href="#i-chevron"/></svg></summary>
                    <p>Colando uma linha de código no seu site. Para usar segmentação por usuário, são três linhas: um objeto de configuração com os atributos do usuário e o script.</p>
                </details>
                <details class="faq-item reveal">
                    <summary>Funciona com qualquer stack? <svg class="icon" aria-hidden="true"><use href="#i-chevron"/></svg></summary>
                    <p>Sim. É um script simples, funciona em qualquer site ou aplicação web, independentemente da tecnologia.</p>
                </details>
            </div>
        </div>
    </section>

    {{-- ============ 3.10 CTA final ============ --}}
    <section class="section section-soft cta-final">
        <div class="blob blob-a" aria-hidden="true"></div>
        <div class="container reveal">
            <h2>Comece a anunciar hoje.<br>De graça.</h2>
            <a class="btn btn-primary btn-lg" href="{{ route('register') }}">Criar conta grátis</a>
            <p class="cta-micro">Leva menos de 1 minuto. Sem cartão de crédito.</p>
        </div>
    </section>

</main>

{{-- ============ 3.11 Footer ============ --}}
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <img src="{{ asset('img/Novidda_Logo_font_white.png') }}" alt="Novidda" width="140" height="35">
                <p>Anuncie novos recursos no seu produto, promova engajamento e mantenha seus usuários atualizados — de maneira simples e sem esforço.</p>
            </div>
            <div>
                <h3>Produto</h3>
                <ul>
                    <li><a href="#funcionalidades">Funcionalidades</a></li>
                    <li><a href="#como-funciona">Como funciona</a></li>
                    <li><a href="#gratuito">Por que gratuito</a></li>
                    <li><a href="#faq">FAQ</a></li>
                </ul>
            </div>
            <div>
                <h3>Conta</h3>
                <ul>
                    <li><a href="{{ route('login') }}">Entrar</a></li>
                    <li><a href="{{ route('register') }}">Criar conta grátis</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">&copy; {{ date('Y') }} Novidda. Todos os direitos reservados.</div>
    </div>
</footer>

<script>
(function () {
    // Headline: palavra digitada/apagada em loop
    var twEl = document.getElementById('hero-typewriter');
    if (twEl) {
        var words = ['novidades', 'melhorias', 'hotfixes', 'roadmap'];
        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduceMotion) {
            twEl.textContent = words[0];
        } else {
            var wordIndex = 0;
            var TYPE_MS = 70, DELETE_MS = 40, HOLD_MS = 1600, GAP_MS = 300;
            var type = function (word, i) {
                twEl.textContent = word.slice(0, i);
                if (i < word.length) {
                    setTimeout(function () { type(word, i + 1); }, TYPE_MS);
                } else {
                    setTimeout(function () { erase(word, word.length); }, HOLD_MS);
                }
            };
            var erase = function (word, i) {
                twEl.textContent = word.slice(0, i);
                if (i > 0) {
                    setTimeout(function () { erase(word, i - 1); }, DELETE_MS);
                } else {
                    wordIndex = (wordIndex + 1) % words.length;
                    setTimeout(function () { type(words[wordIndex], 0); }, GAP_MS);
                }
            };
            type(words[0], 0);
        }
    }

    // Nav: sombra ao rolar
    var nav = document.getElementById('nav');
    var onScroll = function () {
        nav.classList.toggle('scrolled', window.scrollY > 24);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    // Scroll reveal
    var revealEls = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
        revealEls.forEach(function (el) { io.observe(el); });
    } else {
        revealEls.forEach(function (el) { el.classList.add('in'); });
    }

    // Copiar snippet de instalação (com fallback de clipboard)
    var copyBtn = document.getElementById('copy-snippet');
    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            var text = document.getElementById('install-snippet').textContent;
            var done = function () {
                copyBtn.classList.add('copied');
                copyBtn.querySelector('use').setAttribute('href', '#i-check');
                setTimeout(function () {
                    copyBtn.classList.remove('copied');
                    copyBtn.querySelector('use').setAttribute('href', '#i-copy');
                }, 2000);
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(done).catch(function () { fallback(); });
            } else {
                fallback();
            }
            function fallback() {
                var ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                try { document.execCommand('copy'); done(); } catch (e) {}
                document.body.removeChild(ta);
            }
        });
    }
})();
</script>

</body>
</html>
