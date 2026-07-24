# Novidda — Contador de Não-Lidos por Usuário

> Especificação de implementação para tornar o estado de "lido/não-lido" **por usuário do sistema hospedeiro**, e não por navegador.
> Resolve: contador compartilhado entre logins diferentes no mesmo navegador e contador zerado incorretamente para novos usuários.

---

## 0. Causa raiz confirmada (atualização) — descompasso de contrato

Investigação posterior revelou o motivo real de o problema persistir mesmo após as primeiras correções: **existiam três mecanismos de configuração desalinhados**.

| Onde | O que espera | Realidade antes desta correção |
|---|---|---|
| Documentação ([resources/views/embed/show.blade.php](../../resources/views/embed/show.blade.php)) | `window.noviddaConfig = { token, user: { id, email, ... } }` | É o que os clientes copiam |
| `widget.js` | `data-token` / `?token=` (+ `data-user-id`) | **Ignorava `window.noviddaConfig` completamente** |
| Backend | `user_id` ou `user.id` no request | Pronto, mas nunca recebia a identidade |

Consequência: ao seguir a documentação e configurar `noviddaConfig.user.id`, o `widget.js` **descartava** essa informação. A identidade do usuário nunca chegava ao backend, então todos caíam no `reader_id` anônimo do `localStorage` (por navegador).

Isso explica o sintoma "**usuário novo aparece com tudo lido**": no mesmo navegador em que outro usuário já abriu o widget, o `localStorage` já tinha um `reader_id` com leituras gravadas; o novo login herdava esse mesmo `reader_id`.

**Correção-chave:** alinhar o `widget.js` ao contrato documentado `window.noviddaConfig` e derivar a identidade de leitura de `noviddaConfig.user.id` (fallback `user.email`). Ver §4.3.

**Resposta à pergunta "usar o id do usuário é boa estratégia?":** sim — o `user.id` canônico é a chave correta. Cada id tem seu próprio conjunto de lidos; usuário novo = id novo = zero leituras = todas as novidades como não-lidas.

---

## 1. Problema

O widget Novidda é embutido em um sistema externo (ex.: `XPTO.com`) via `<script>`. Cada usuário do XPTO tem login próprio (email + senha). A premissa do produto é: **cada usuário deve ver as novidades de forma individual** — o que ele já leu some do contador; o que foi publicado depois aparece como não-lido.

Comportamento esperado:

| Ação | Resultado esperado |
|---|---|
| Usuário 1 loga | vê 4 não-lidas |
| Usuário 2 loga | vê 4 não-lidas (independente do usuário 1) |
| Usuário 1 abre o painel | contador do usuário 1 zera |
| Usuário 2 **não** abre | contador do usuário 2 permanece 4 |
| Publica-se +1 changelog no Novidda | usuário 3 (que já tinha zerado) passa a ver 1 não-lida |

Comportamento atual (bug): o contador é compartilhado por navegador. Quando um usuário zera, outro login no mesmo navegador aparece zerado também.

---

## 2. Causa raiz

O estado de leitura é indexado por **`reader_id`**, um identificador **aleatório gerado e salvo no `localStorage` do navegador** (chave `novidda_reader`) — ver [public/widget.js](../../public/widget.js) função `readerId()`.

Consequências:

- **Por navegador, não por usuário.** Vários logins no mesmo navegador → mesmo `reader_id` → estado de leitura compartilhado.
- **Não segue o usuário.** O mesmo usuário em outro navegador/dispositivo/aba anônima → `reader_id` novo → tudo volta a aparecer como não-lido.
- **A identidade real do usuário do XPTO nunca chegava ao backend** nas chamadas de contagem/leitura.

### 2.1 Restrição fundamental (por que não há como o widget "adivinhar" o usuário)

O widget é um **script de terceiro** rodando dentro do XPTO.com. Ele **não tem acesso à sessão, cookies de login ou banco do XPTO** — isolamento imposto pelo navegador. Portanto:

> **O widget só consegue distinguir usuários se o próprio XPTO.com informar quem está logado, dinamicamente, a cada carregamento de página.**

Um `<script>` **estático** (o mesmo HTML para todos) é o cerne do problema atual — ele não carrega nenhuma identidade. Mesmo com suporte a `data-user-id` no widget, se o XPTO renderizar o snippet igual para todo mundo (sem preencher o id do usuário logado), o problema continua.

---

## 3. Fluxo técnico atual

Arquivos envolvidos:

- **[public/widget.js](../../public/widget.js)** — loader (botão flutuante + badge). Chama `GET /unread-count`.
- **[public/widget-app.js](../../public/widget-app.js)** — painel. Chama `GET /feed` e `POST /read`.
- **[app/Http/Controllers/Api/WidgetApiController.php](../../app/Http/Controllers/Api/WidgetApiController.php)** — endpoints.
- **[app/Http/Middleware/ResolveWidgetToken.php](../../app/Http/Middleware/ResolveWidgetToken.php)** — resolve `token` → `account` (tenant).
- **Tabela `reads`** — [migration](../../database/migrations/2024_01_01_000011_create_reads_table.php): `unique(reader_id, changelog_id)`. Uma linha por (leitor, changelog) lido.

Cálculo de não-lidos (`unreadCount`): `não_lidos = changelogs_publicados − reads_do_reader`. Ao abrir o painel, `POST /read` grava um `read` para cada changelog publicado daquele `reader_id`.

---

## 4. Solução

### 4.1 Princípio

Trocar a identidade de leitura de **navegador (`reader_id`)** para **usuário do sistema hospedeiro (`user_id`)**, mantendo o modo anônimo como fallback para sites públicos.

O `reader_id` efetivo passa a ser derivado no servidor:

```
se veio user_id  →  reader efetivo = "u-" + md5(account_id + "|" + user_id)
senão            →  reader efetivo = reader_id anônimo (localStorage)  [fallback]
```

Propriedades:
- **Distinto por login** — usuários diferentes no mesmo navegador têm readers diferentes.
- **Estável entre dispositivos** — o mesmo usuário vê o mesmo estado em qualquer navegador.
- **Isolado por conta** (`account_id` no hash) — sem colisão entre tenants.
- **Sem migration de schema** — a coluna `reader_id` (64 chars) comporta `u-` + 32 hex.

### 4.2 O contrato de integração (lado XPTO.com) — **é aqui que o problema se resolve**

O XPTO.com **deve** identificar o usuário logado usando o contrato **documentado** `window.noviddaConfig`, **renderizado dinamicamente pelo servidor** a cada página. O identificador deve ser **estável e único por usuário** — use o **id interno** (canônico `user.id`); o **email** serve de alternativa.

**Contrato oficial — `window.noviddaConfig` (definido ANTES do script):**

```html
<!-- ERRADO: estático, sem usuário → todos compartilham o mesmo estado -->
<script src="http://127.0.0.1:8000/widget.js"
        data-token="lGcf3pzCkAFiWqYd9pMfIM7VV2GtY6KMph8LmZAC" async></script>

<!-- CORRETO: id do usuário logado renderizado pelo servidor do XPTO -->
<script>
  window.noviddaConfig = {
    token: 'lGcf3pzCkAFiWqYd9pMfIM7VV2GtY6KMph8LmZAC',
    user: {
      id:    '<?= $usuarioLogado->id ?>',      // chave da identidade de leitura
      email: '<?= $usuarioLogado->email ?>',   // fallback e moderação de comentários
      name:  '<?= $usuarioLogado->name ?>'
      // ...demais atributos canônicos/personalizados p/ segmentação
    }
  };
</script>
<script src="http://127.0.0.1:8000/widget.js" async></script>
```

> **Ponto crítico:** o valor de `user.id` **não pode ser fixo**. Precisa ser o id do usuário logado naquela sessão. Se for igual (ou ausente) para todos, o bug persiste — porque para o widget todos serão "a mesma pessoa".

Este é exatamente o snippet "Com identificação de usuário — recomendado" da tela **Instalação & Documentação** ([show.blade.php](../../resources/views/embed/show.blade.php)). Fallbacks legados (`data-user-id`, `window.noviddaSettings`) continuam aceitos, mas `noviddaConfig` é a fonte de verdade.

### 4.3 O que já está implementado no lado Novidda

**Backend — [WidgetApiController.php](../../app/Http/Controllers/Api/WidgetApiController.php):**
- `readerId()` prioriza `user_id` (ou `user.id`) e retorna `u-md5(account_id|user_id)`; sem isso, mantém `reader_id`/anon.
- `unreadCount()` passou a usar `$this->readerId($request)` — antes lia `reader_id` cru. Assim contagem, leitura, reação, comentário e feedback ficam por usuário automaticamente.

**Loader — [widget.js](../../public/widget.js):**
- **Lê `window.noviddaConfig`** para `token` (fallback `data-token`/`?token=`) e para `user` (fallback `data-user-id`/`noviddaSettings`).
- Deriva `userId` de `noviddaConfig.user.id` (fallback `user.email`).
- Envia `user_id` no `GET /unread-count`; propaga `window.__novidda.userId` e `window.__novidda.user`.

**Painel — [widget-app.js](../../public/widget-app.js):**
- Helpers `ident()` / `identQS()` anexam `reader_id` + `user_id` em `/read`, `/feed`, `/reaction`, `/comment`, `/changelog-feedback`, `/roadmap-vote`, `/roadmap-feedback`.

### 4.4 Pendente relacionado — segmentação do feed

O objeto `user` completo (para segmentação por atributos) ainda **não** é enviado ao `GET /feed`. Hoje só o `user_id` (escalar) trafega. Enquanto a documentação promete segmentação via `noviddaConfig.user`, o `feed` precisa passar a receber o objeto `user` (via `POST /feed` com o objeto no corpo, pois pode exceder o limite de URL). Não afeta o contador — é o próximo passo para completar o contrato documentado.

---

## 5. Passos de implementação

### 5.1 Lado Novidda (produto)
- [x] `readerId()` derivar de `user_id` com namespace por conta.
- [x] `unreadCount()` usar `readerId()`.
- [x] `widget.js` **ler `window.noviddaConfig`** (token + user) e derivar `userId` de `user.id`/`user.email`.
- [x] `widget-app.js` propagar `user_id` em todas as chamadas relevantes.
- [ ] **Alinhar a documentação/instalação:** o snippet mínimo (`data-token`, sem usuário) deve trazer um aviso de que não há rastreio por usuário. Destacar `noviddaConfig.user` como o caminho recomendado (§4.2).
- [ ] Enviar o objeto `user` completo ao `feed` para completar a segmentação (§4.4).
- [ ] (Opcional) Assinatura HMAC — ver §7.
- [ ] (Opcional) Banner contextual "once_per_user" também por usuário — hoje é por `localStorage` (§8).

### 5.2 Lado cliente (XPTO.com) — **imprescindível**
- [ ] Definir `window.noviddaConfig = { token, user: { id, email, ... } }` **antes** do `<script src="widget.js">`, renderizado dinamicamente pelo servidor.
- [ ] Garantir que `user.id` seja único e estável por usuário (id interno preferível; email aceitável).
- [ ] Ao trocar de usuário (logout/login), a nova página deve renderizar o novo `user.id`.

---

## 6. Plano de verificação

Cenário de teste com 3 usuários no **mesmo navegador**:

1. XPTO renderiza `window.noviddaConfig = { token, user: { id: '1' } }` para o usuário 1 → widget mostra 4 não-lidas.
2. Abrir painel → `POST /read` grava `u-md5(account|1)` → badge zera.
3. Logout/login como usuário 2 → config renderiza `user.id = '2'` → widget mostra **4 não-lidas** (estado independente). ✅
4. Usuário 2 **não** abre → permanece 4.
5. Publicar +1 changelog no Novidda.
6. Login como usuário 3 (que já havia zerado) → widget mostra **1 não-lida** (só o novo). ✅
7. **Usuário recém-criado** (id inédito) → widget mostra **todas** as novidades como não-lidas. ✅

Verificações complementares:
- Inspecionar a tabela `reads`: devem existir linhas com `reader_id` no formato `u-<hash>` distintas por usuário.
- `GET /api/v1/widget/{token}/unread-count?user_id=2` deve retornar contagem específica do usuário 2.
- Sem `noviddaConfig.user` (site público): comportamento anônimo por navegador preservado.

---

## 7. Segurança (opcional, recomendado para produção)

O `user_id` é confiado do cliente (assim como o `reader_id` sempre foi). Um usuário poderia forjar o id de outro e ver/zerar o estado alheio. Impacto é baixo (é só estado de leitura de novidades), mas para blindar:

- XPTO gera, no backend, um HMAC: `sig = HMAC_SHA256(segredo_compartilhado, account_id + "|" + user_id)`.
- Passa `data-user-id` + `data-user-sig`.
- Novidda valida a assinatura antes de aceitar o `user_id`.

Fica como fase 2 — não bloqueia a correção funcional.

---

## 8. Fora de escopo (itens relacionados)

- **Banner contextual `once_per_user`** ([widget-contextual.js](../../public/widget-contextual.js)) — hoje o "já mostrado" é controlado por `localStorage`, tendo a mesma limitação por navegador. Deveria migrar para a mesma identidade `user_id`. Feature separada.
- **Reset único no deploy** — como a identidade muda de `r-xxx` → `u-xxx`, na primeira vez os usuários verão as novidades atuais como não-lidas uma vez. Comportamento aceitável; sem migration.

---

## 9. Resumo executivo

A causa raiz real era um **descompasso de contrato**: o `widget.js` ignorava o `window.noviddaConfig` que a própria documentação manda usar, então o id do usuário nunca chegava ao backend (§0). Isso foi corrigido — o `widget.js` agora lê `noviddaConfig` e deriva a identidade de leitura de `user.id`.

Ainda assim, **o passo decisivo é do lado XPTO.com**: definir `window.noviddaConfig.user.id` dinamicamente com o usuário logado (§4.2). Com o snippet estático atual (só `data-token`, sem `user`), não há como distinguir usuários e o contador continua por navegador. Usar o `user.id` é a estratégia correta para saber quem já leu e quem não leu, e faz o usuário recém-criado ver tudo como não-lido.
