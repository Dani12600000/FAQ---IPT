# FAQ do IPT — briefing do projeto

> Contexto para o Claude Code. Guarda este ficheiro como `CLAUDE.md` na raiz do projeto.

## O que é

FAQ para o Instituto Politécnico de Tomar (IPT), feito no âmbito de voluntariado com o CIS (Centro de Informática e Sistemas), supervisionado pelo Dr. Joaquim Pombo.

- **Agora:** prova de conceito **standalone** (site próprio + backoffice) para demonstrar.
- **Depois:** integrar no site do IPT. A forma ainda não está decidida, por isso a **API JSON é obrigatória desde o início**.
- Quem vai manter o projeto no futuro é o CIS → código simples, convencional e bem organizado, "à maneira do Laravel". Nada de soluções exóticas.

## Ideia central

Cada resposta é escrita **uma vez** e aparece em todos os sítios onde faz falta.

```
Serviço ──N:M── Categoria ──N:M── Tema ──N:M── Pergunta (+ 1 resposta)
```

- Um serviço tem várias categorias; uma categoria pode estar em vários serviços.
- Uma categoria tem vários temas; um tema pode estar em várias categorias.
- Um tema tem várias perguntas; uma pergunta pode estar em vários temas.
- Cada pergunta tem exatamente 1 resposta → a resposta é uma **coluna** da pergunta, não uma tabela.

## Stack

| Área | Escolha |
| --- | --- |
| Linguagem | PHP 8.2+ |
| Framework | Laravel (última versão estável) |
| Backoffice | Filament (versão compatível com essa versão do Laravel) |
| Base de dados | MySQL 8.4 LTS |
| Front-end público | Blade + JavaScript simples (sem framework SPA) |
| CSS | Simples e fácil de adaptar à identidade do IPT mais tarde |

Antes de instalar pacotes, confirma as versões atuais e a compatibilidade entre Laravel e Filament.

## Ambiente de desenvolvimento

- Windows 11, x64.
- MySQL Community Server **8.4.13 LTS** em `localhost:3306`, utilizador `root`. A base de dados `faq_ipt` **já existe** (utf8mb4 / utf8mb4_unicode_ci).
- MySQL Workbench instalado.
- PHP/Composer: verificar com `php -v` e `composer -V`. Se faltar, a recomendação é o Laravel Herd (versão gratuita; o MySQL já está instalado à parte).

`.env`:
```
APP_LOCALE=pt
APP_FALLBACK_LOCALE=pt
APP_TIMEZONE=Europe/Lisbon
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=faq_ipt
DB_USERNAME=root
DB_PASSWORD=   # o Daniel preenche
```

## Estrutura de dados

| Tabela | Campos |
| --- | --- |
| `servicos` | id, nome, slug (único), descricao (nullable), ordem, ativo, timestamps |
| `categorias` | id, nome, slug (único), descricao (nullable), ativo, timestamps |
| `temas` | id, nome, slug (único), descricao (nullable), ativo, timestamps |
| `perguntas` | id, pergunta, slug (único), resposta (longText, HTML), publicada, timestamps |
| `servico_categoria` | servico_id, categoria_id, ordem — chave única no par |
| `categoria_tema` | categoria_id, tema_id, ordem — chave única no par |
| `tema_pergunta` | tema_id, pergunta_id, ordem — chave única no par |
| `pesquisas` | id, termo, resultados (int), created_at |
| `avaliacoes` | id, pergunta_id, util (bool), created_at |
| `users` | padrão do Laravel + `papel` (admin, editor) |

Regras:
- **A ordem fica nas tabelas de ligação**, não nas entidades. A mesma pergunta pode ser a 1.ª num tema e a 5.ª noutro.
- Relações `belongsToMany` com `->withPivot('ordem')->orderByPivot('ordem')`.
- Itens com `ativo = false` / `publicada = false` não aparecem no site nem na API.
- Slugs gerados a partir do nome, editáveis.
- Possível versão em inglês no futuro (pergunta pendente). Não implementar agora, mas não fazer escolhas que a impeçam.

## Site público

Rotas (o URL guarda o caminho por onde a pessoa chegou):

```
/                                              lista de serviços + pesquisa
/pesquisa?q=                                   pesquisa global
/{servico}                                     categorias do serviço
/{servico}/{categoria}                         temas da categoria
/{servico}/{categoria}/{tema}                  perguntas do tema (acordeão)
/{servico}/{categoria}/{tema}/{pergunta}       página da pergunta, com breadcrumb deste caminho
/p/{pergunta}                                  URL canónico da pergunta
```

- **Validar a cadeia:** a categoria tem de pertencer ao serviço, o tema à categoria, a pergunta ao tema. Caso contrário, 404.
- A página da pergunta tem `<link rel="canonical">` para `/p/{pergunta}`.
- Breadcrumb em todas as páginas.
- **"Também aparece em":** os outros caminhos onde a pergunta está.
- **"Esta resposta foi útil?"** (sim/não) → grava em `avaliacoes`, sem contas e sem cookies de rastreio.
- **Pesquisa global** em pergunta + resposta (texto sem HTML), com resultados enquanto se escreve (debounce). Cada pesquisa grava em `pesquisas` com o número de resultados.
- Marcação **schema.org FAQPage** (JSON-LD) nas páginas de tema e de pergunta.
- Acessível (teclado, leitor de ecrã, contraste) e responsivo.
- Todo o texto da interface em **português de Portugal**.

## API JSON (só leitura)

```
GET /api/servicos
GET /api/servicos/{servico}            árvore completa: categorias → temas → perguntas
GET /api/perguntas/{pergunta}
GET /api/pesquisa?q=
```

- Usar API Resources do Laravel.
- Só conteúdo ativo/publicado.
- CORS configurável (para o widget no site do IPT mais tarde).

## Backoffice (Filament)

- CRUD de Serviços, Categorias, Temas e Perguntas.
- **Ligações a partir da própria entidade:** ao editar uma pergunta, escolhem-se os temas onde aparece; ao editar um tema, as categorias; ao editar uma categoria, os serviços.
- **Ordenação por arrastar** dentro de cada serviço, categoria e tema, gravando o `ordem` da tabela de ligação.
- Editor de texto rico para a resposta (negrito, listas, ligações).
- Ativar/desativar e publicar/despublicar sem apagar.
- Pré-visualização da pergunta.
- Painel com:
  - pesquisas sem resultados (o que falta no FAQ);
  - perguntas mais e menos úteis;
  - perguntas sem tema.
- Papéis: **admin** gere tudo, incluindo utilizadores; **editor** gere conteúdo.
- Interface do Filament em português.

## Dados de exemplo (seeders)

- Criar alguns serviços, categorias, temas e perguntas **plausíveis** para um politécnico (por exemplo, Serviços Académicos, Ação Social, Biblioteca, Internacional), com várias ligações partilhadas para demonstrar o N:M.
- **Não inventar informação oficial do IPT** (prazos, valores, regras). As respostas de exemplo devem dizer claramente que são exemplo, por exemplo começando com "Resposta de exemplo:".
- Um utilizador admin para testes (credenciais só no seeder/README, nunca em produção).

## Plano da prova de conceito (~46 h, 8 h/semana)

1. **Fase 0:** levantamento e conteúdo de exemplo (~4 h)
2. **Fase 1:** migrações, modelos, relações e backoffice Filament (~16 h)
3. **Fase 2:** site público — navegação, URLs com caminho, pesquisa (~14 h)
4. **Fase 3:** "foi útil?", registo de pesquisas, schema.org, API JSON (~8 h)
5. **Fase 4:** testes, ajustes e demonstração (~4 h)

## Forma de trabalhar

- O Daniel está a aprender Laravel e Filament neste projeto. Explica as decisões e os conceitos do Laravel quando aparecem pela primeira vez.
- Commits pequenos e com mensagens claras, no formato [Conventional Commits](https://www.conventionalcommits.org/) e **em inglês** (por exemplo `feat: add question preview`, `fix: keep slug when editing`, `docs: add install steps`).
- Testes (Pest ou PHPUnit) pelo menos para:
  - a validação da cadeia de URLs;
  - a ordem nas tabelas de ligação;
  - a pesquisa;
  - a API.
- README com instruções de instalação para quem vier depois (CIS).

## Perguntas ainda em aberto (com o Dr. Joaquim Pombo)

- Que serviços entram na primeira versão? Há perguntas reais para usar?
- Precisa de versão em inglês desde o início?
- Plataforma do site do IPT e forma de integração (página própria, widget ou CMS).
- Versões de PHP e MySQL no servidor do CIS.
- Onde alojar a prova de conceito.
- Login dos editores: contas próprias ou conta do IPT (LDAP/SSO)?
- Guia de identidade visual do IPT.

## Tarefas pendentes (revisão de 9 nov 2026)

A prova de conceito está feita e já vai além do plano (editores por serviço, histórico de versões, perguntas semelhantes, versão própria). Faz estas tarefas **por ordem**, uma de cada vez, com um commit por tarefa (Conventional Commits em inglês). Corre `php artisan test` no fim de cada uma. Quando terminares uma, marca-a aqui com `[x]`.

### 1. Correções

- [x] **Testes a falhar.** Corre `php artisan test` e corrige o que falhar. Na última execução (5 nov) falharam 19, sobretudo em `PermissoesPorServicoTest`, `VersoesPerguntaTest`, `VersaoPropriaTest`, `PainelBackofficeTest`, `BackofficeTest` e `ApiTest` (documentação da API). Se for um problema do ambiente, explica ao Daniel em vez de mudar os testes. Apaga o `tests/Feature/ExampleTest.php` se só testar a página de boas-vindas do Laravel.
- [x] **Congelar a "versão própria".** Não acrescentes mais nada a esta funcionalidade até o Dr. Joaquim Pombo decidir se fica, porque reintroduz respostas duplicadas. Põe-na atrás de uma opção de configuração (`FAQ_VERSAO_PROPRIA`, ligada por omissão), para se poder desligar sem mexer no código.

### 2. Rede do IPT e API

- [x] **Limites de pedidos configuráveis.** Hoje são 10 avaliações e 60 pedidos à API por minuto e por IP. Na rede do IPT muitos utilizadores partilham o mesmo IP, e se o site do IPT chamar a API a partir do servidor, todos os pedidos vêm de um só IP. Passa os limites para o `.env` (com valores por omissão mais altos) e acrescenta uma lista de IPs sem limite (`API_IPS_SEM_LIMITE`), para o servidor do IPT. Documenta no README.
- [x] **CORS fechado em produção.** Hoje, sem `CORS_ALLOWED_ORIGINS`, a API aceita qualquer site. Mantém `*` só em ambiente local; em produção, sem a variável, não aceita nenhuma origem externa. Documenta no README.

### 3. Conteúdo sempre atual

- [x] **Data de revisão das respostas.** Campo opcional "rever até" na pergunta. Widget no painel com as respostas cuja data já passou ou passa nos próximos 30 dias (cada editor vê só as do seu serviço).
- [x] **"Atualizado em".** Mostra na página da pergunta a data da última alteração do texto (a data da última versão, não a de qualquer gravação). Inclui-a também na API.
- [x] **Contactos de cada serviço.** Campos opcionais email, telefone, horário de atendimento e endereço da página do serviço. Mostra-os na página do serviço e, na página da pergunta, quando alguém responde "Não" a "Esta resposta foi útil?" ("Precisa de mais ajuda? Contacte…"). Inclui-os na API.

### 4. Privacidade e manutenção

- [x] **Apagar pesquisas antigas.** As pesquisas guardadas podem ter dados pessoais (nomes, números de aluno). Apaga automaticamente as que tiverem mais de 12 meses (configurável), com `MassPrunable` no modelo `Pesquisa` e `model:prune` no agendador. Documenta o cron no README.
- [x] **`sitemap.xml` e `robots.txt`**, com os serviços, categorias e temas ativos e as perguntas publicadas (pelo URL canónico `/p/{pergunta}`).

### Não fazer ainda (depende de decisões)

- Identidade visual do IPT: espera pelo guia.
- Versão em inglês, integração no site do IPT e login dos editores (contas próprias ou conta do IPT): esperam pelas respostas do Dr. Joaquim Pombo.
