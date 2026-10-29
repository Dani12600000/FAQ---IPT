# FAQ do IPT

Perguntas frequentes do Instituto Politécnico de Tomar: site público, backoffice para editar o conteúdo e API JSON para integrar noutros sites.

Prova de conceito feita em voluntariado com o CIS (Centro de Informática e Sistemas). **O conteúdo incluído é apenas de exemplo**: não contém informação oficial do IPT.

## Ideia central

Cada resposta é escrita **uma vez** e aparece em todos os sítios onde faz falta.

```
Serviço ──N:M── Categoria ──N:M── Tema ──N:M── Pergunta (+ 1 resposta)
```

A mesma pergunta pode estar em vários temas, o mesmo tema em várias categorias e a mesma categoria em vários serviços. A **ordem** de cada item é guardada na ligação, por isso a mesma pergunta pode ser a 1.ª num tema e a 5.ª noutro.

## Índice

1. [Tecnologias](#tecnologias)
2. [Instalação para desenvolvimento](#instalação-para-desenvolvimento)
3. [Contas de teste](#contas-de-teste)
4. [Onde está cada coisa](#onde-está-cada-coisa)
5. [Base de dados](#base-de-dados)
6. [Site público](#site-público)
7. [Backoffice](#backoffice)
8. [API JSON](#api-json)
9. [Testes](#testes)
10. [Pôr em produção](#pôr-em-produção)
11. [Personalizar o aspeto](#personalizar-o-aspeto)
12. [Decisões e limitações conhecidas](#decisões-e-limitações-conhecidas)

## Tecnologias

| Área | Escolha |
| --- | --- |
| Linguagem | PHP **8.3 ou superior** (exigido pelo Laravel 13) |
| Framework | Laravel 13 |
| Backoffice | Filament 5 |
| Base de dados | MySQL 8.4 LTS (utf8mb4 / utf8mb4_unicode_ci) |
| Site público | Blade + CSS e JavaScript simples, **sem passo de compilação** (sem npm/Vite) |
| Testes | PHPUnit |

Extensões de PHP necessárias: as do [Laravel](https://laravel.com/docs/13.x/deployment#server-requirements) mais `pdo_mysql`, `intl` e `zip` (o Filament usa-as).

## Instalação para desenvolvimento

Pré-requisitos: PHP 8.3+, [Composer](https://getcomposer.org), Git e MySQL 8.4. Em Windows, o [Laravel Herd](https://herd.laravel.com) instala PHP e Composer de uma vez (não é preciso ativar o MySQL do Herd se já houver um MySQL instalado).

1. Obter o código e instalar as dependências:

   ```bash
   git clone <endereço-do-repositório> faq-ipt
   cd faq-ipt
   composer install
   ```

   O `composer install` também publica os ficheiros do Filament em `public/` (comando `filament:upgrade`).

2. Criar a base de dados vazia no MySQL:

   ```sql
   CREATE DATABASE faq_ipt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. Criar o `.env` a partir do exemplo e gerar a chave da aplicação:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   No `.env`, preencher pelo menos `DB_PASSWORD` (e `DB_USERNAME` se não for `root`).

4. Criar as tabelas e o conteúdo de exemplo:

   ```bash
   php artisan migrate --seed
   ```

5. Arrancar o servidor de desenvolvimento:

   ```bash
   php artisan serve
   ```

   - Site público: <http://localhost:8000>
   - Backoffice: <http://localhost:8000/admin>
   - API: <http://localhost:8000/api/servicos>

Para recomeçar do zero (apaga **todos** os dados): `php artisan migrate:fresh --seed`.

## Contas de teste

Criadas pelo seeder **só fora de produção** (`APP_ENV` diferente de `production`):

| Email | Papel | Palavra-passe |
| --- | --- | --- |
| `admin@ipt.test` | Administrador | `password` |
| `editor@ipt.test` | Editor | `password` |

O `php artisan db:seed` pode correr várias vezes: não duplica contas nem conteúdo.

## Onde está cada coisa

```
app/
  Models/                       Servico, Categoria, Tema, Pergunta, Pesquisa, Avaliacao, User
                                + ServicoCategoria, CategoriaTema, TemaPergunta (tabelas de ligação)
  Http/Controllers/
    FaqController.php           páginas públicas e validação da cadeia de URLs
    PesquisaController.php      página de pesquisa (e resultados enquanto se escreve)
    AvaliacaoController.php     "Esta resposta foi útil?"
    Api/                        controladores da API JSON
  Http/Resources/               formato do JSON da API (API Resources)
  Services/PesquisaFaq.php      lógica da pesquisa, partilhada pelo site e pela API
  Filament/
    Resources/                  backoffice: um recurso por entidade (formulário, tabela, páginas)
    Widgets/                    painel de estatísticas do backoffice
    Forms/CamposComSlug.php     campo de nome + slug gerado automaticamente
  Policies/UserPolicy.php       só os administradores gerem utilizadores
  Enums/Papel.php               papéis: admin e editor
database/
  migrations/                   estrutura da base de dados
  seeders/                      contas de teste e conteúdo de exemplo
resources/views/
  components/                   layout, breadcrumb, caixa de pesquisa, "foi útil?", JSON-LD
  faq/                          páginas do site público
  errors/404.blade.php          página "não encontrada" em português
public/css/site.css             estilos do site público
public/js/                      pesquisa enquanto se escreve e "foi útil?" (opcionais)
routes/web.php                  rotas do site público
routes/api.php                  rotas da API
lang/pt/ e lang/vendor/         traduções em português de Portugal
tests/Feature/                  testes
```

## Base de dados

| Tabela | Campos principais |
| --- | --- |
| `servicos` | nome, slug (único), descricao, **ordem** (ordem na página inicial), ativo |
| `categorias` | nome, slug, descricao, ativo |
| `temas` | nome, slug, descricao, ativo |
| `perguntas` | pergunta, slug, resposta (HTML), resposta_texto (sem HTML, para a pesquisa), publicada |
| `servico_categoria` | servico_id, categoria_id, **ordem** (chave primária no par) |
| `categoria_tema` | categoria_id, tema_id, **ordem** |
| `tema_pergunta` | tema_id, pergunta_id, **ordem** |
| `pesquisas` | termo, resultados, created_at |
| `avaliacoes` | pergunta_id, util (sim/não), created_at |
| `users` | campos do Laravel + papel (`admin` ou `editor`) |

Regras:

- **A ordem está nas tabelas de ligação.** Nos modelos, as relações usam `->withPivot('ordem')->orderByPivot('ordem')`. Quando se liga um item sem indicar a ordem, ele vai para o fim da lista (ver os modelos `ServicoCategoria`, `CategoriaTema` e `TemaPergunta`).
- **Ativo / publicada:** o que está desativado ou por publicar não aparece no site nem na API, mas não é apagado. Os filtros estão nos modelos: `Servico::ativos()`, `Categoria::ativas()`, `Tema::ativos()` e `Pergunta::publicadas()`.
- **Apagar** um item apaga também as suas ligações e, no caso das perguntas, as avaliações.
- **`resposta_texto`** é preenchida automaticamente pelo modelo `Pergunta` sempre que a resposta muda.

## Site público

O URL guarda o caminho por onde a pessoa chegou:

| Endereço | Página |
| --- | --- |
| `/` | serviços + pesquisa |
| `/pesquisa?q=` | pesquisa |
| `/{servico}` | categorias do serviço |
| `/{servico}/{categoria}` | temas da categoria |
| `/{servico}/{categoria}/{tema}` | perguntas do tema (acordeão) |
| `/{servico}/{categoria}/{tema}/{pergunta}` | página da pergunta, com o breadcrumb deste caminho |
| `/p/{pergunta}` | endereço canónico da pergunta |

- **Validação da cadeia:** a categoria tem de pertencer ao serviço, o tema à categoria e a pergunta ao tema, e tudo tem de estar ativo/publicado. Caso contrário, a página dá 404 (ver `FaqController`).
- A página da pergunta tem `<link rel="canonical">` para `/p/{pergunta}` e mostra **"Também aparece em"** com os outros caminhos.
- **"Esta resposta foi útil?"** grava sim/não em `avaliacoes`, sem contas e sem cookies de rastreio. Há um limite de 10 avaliações por minuto por IP.
- **Pesquisa** na pergunta e na resposta (sem HTML). Os resultados aparecem enquanto se escreve. Cada pesquisa fica registada em `pesquisas` com o número de resultados, mas enquanto se escreve só é registada quando a pessoa pára 1,5 s (para não guardar "pro", "propi"…).
- **schema.org FAQPage** (JSON-LD) nas páginas de tema e de pergunta.
- **Sem JavaScript tudo funciona:** a pesquisa envia o formulário e o "foi útil?" recarrega a página.
- **Acessibilidade:** ligação "Saltar para o conteúdo", foco visível, acordeão com `<details>` (funciona com teclado), número de resultados anunciado aos leitores de ecrã e contraste AA.

**Slugs reservados:** como `/{servico}` ocupa o primeiro nível do endereço, um serviço não pode ter o slug `admin`, `api`, `p`, `pesquisa`, `up`, `livewire`, `storage`, `css`, `js`, `fonts` ou `filament`. O backoffice recusa estes slugs (lista em `Servico::SLUGS_RESERVADOS`). Se se criar uma nova rota de primeiro nível em `routes/web.php`, é preciso acrescentá-la a essa lista.

## Backoffice

Em `/admin` (Filament).

- **Serviços, Categorias, Temas e Perguntas:** criar, editar, ativar/desativar e publicar/despublicar (diretamente na lista).
- **Ligações a partir da própria entidade:** na pergunta escolhem-se os temas, no tema as categorias e na categoria os serviços.
- **Ordenar:** dentro de um serviço, categoria ou tema, na tabela do fundo da página de edição, botão **"Reordenar registos"**. Arrasta-se ou usam-se as setas do teclado. A lista de serviços também se reordena.
- **Resposta** com editor de texto rico (negrito, itálico, ligações, títulos, listas).
- **Pré-visualizar** a pergunta tal como está escrita, mesmo antes de gravar, e **Ver no site** quando está publicada.
- **Painel de Controlo:** pesquisas sem resultados, perguntas mais e menos úteis, perguntas sem tema.
- **Papéis:** o **administrador** gere tudo, incluindo utilizadores. O **editor** gere só o conteúdo e não vê o menu Utilizadores.

## API JSON

Pública, só de leitura, com o prefixo `/api`. Só devolve conteúdo ativo/publicado. Limite: 60 pedidos por minuto por IP. As respostas vêm dentro de `data`, como é habitual nas API Resources do Laravel.

| Pedido | Devolve |
| --- | --- |
| `GET /api/servicos` | serviços ativos, pela ordem da página inicial |
| `GET /api/servicos/{servico}` | árvore completa do serviço: categorias → temas → perguntas, pela ordem de cada ligação |
| `GET /api/perguntas/{pergunta}` | pergunta, resposta e todos os caminhos onde aparece |
| `GET /api/pesquisa?q={termo}` | perguntas com todas as palavras do termo (mínimo 2 caracteres, máximo 20 resultados) |

Os identificadores são os **slugs**. Um slug que não exista, ou que esteja inativo, dá 404 com `{"message": "..."}`.

Exemplo, `GET /api/perguntas/como-renovo-um-emprestimo`:

```json
{
  "data": {
    "slug": "como-renovo-um-emprestimo",
    "pergunta": "Como renovo um empréstimo?",
    "resposta": "<p><strong>Resposta de exemplo:</strong> ...</p>",
    "resposta_texto": "Resposta de exemplo: ...",
    "url": "http://localhost:8000/p/como-renovo-um-emprestimo",
    "caminhos": [
      {
        "servico": { "slug": "biblioteca", "nome": "Biblioteca" },
        "categoria": { "slug": "emprestimos", "nome": "Empréstimos" },
        "tema": { "slug": "requisicao-e-renovacao", "nome": "Requisição e renovação" },
        "url": "http://localhost:8000/biblioteca/emprestimos/requisicao-e-renovacao/como-renovo-um-emprestimo"
      }
    ]
  }
}
```

Notas para quem integrar:

- **`resposta`** é HTML já limpo (sem scripts nem atributos de eventos) e pode ser inserido diretamente. **`resposta_texto`** é a mesma resposta sem HTML.
- Na árvore, cada categoria, tema e pergunta tem um campo **`ordem`**: a posição dentro do pai.
- **Registo de pesquisas:** `GET /api/pesquisa` **só regista** a pesquisa nas estatísticas com `&registar=1`. Num widget com pesquisa enquanto se escreve, deve enviar-se `registar=1` só quando a pessoa submete ou pára de escrever.
- **CORS:** os sites que podem chamar a API a partir do browser configuram-se no `.env`, separados por vírgulas:

  ```
  CORS_ALLOWED_ORIGINS=https://www.ipt.pt,https://portal.ipt.pt
  ```

  O valor `*` (por defeito) aceita qualquer site.

## Testes

```bash
php artisan test
```

Cobrem, entre outros: a validação da cadeia de URLs, a ordem nas tabelas de ligação, a pesquisa e o seu registo, a API, o "foi útil?", o JSON-LD, as permissões do backoffice, o painel e o conteúdo de exemplo.

Os testes correm numa base **SQLite em memória** (ver `phpunit.xml`): são rápidos e não tocam no MySQL. **Atenção:** o SQLite não se comporta exatamente como o MySQL:

- não ignora acentos na pesquisa;
- aceita consultas `GROUP BY` que o MySQL recusa.

Antes de uma alteração importante, convém confirmar também no browser contra o MySQL.

Estilo do código (formatador oficial do Laravel):

```bash
vendor/bin/pint
```

## Pôr em produção

Resumo dos passos específicos deste projeto. O resto segue o [guia de deployment do Laravel](https://laravel.com/docs/13.x/deployment).

1. No `.env` de produção:

   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://<endereço-real>
   CORS_ALLOWED_ORIGINS=https://www.ipt.pt
   ```

2. Instalar e preparar:

   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   php artisan optimize
   ```

   **Não correr os seeders em produção.** Em produção eles não criam nada, mas também não são precisos.

3. O servidor web deve apontar para a pasta `public/`.

4. Criar o primeiro administrador. O comando do Filament cria o utilizador com o papel **editor**, por isso é preciso promovê-lo:

   ```bash
   php artisan make:filament-user
   php artisan tinker --execute="App\Models\User::where('email', 'email@ipt.pt')->update(['papel' => 'admin']);"
   ```

   A partir daí, os outros utilizadores criam-se no backoffice, em **Administração → Utilizadores**.

## Personalizar o aspeto

- **Site público:** as cores e o tipo de letra são variáveis no topo de `public/css/site.css` (`--cor-primaria`, etc.). As cores atuais são provisórias, à espera do guia de identidade visual do IPT. Ao mudá-las, convém manter o contraste mínimo de 4,5:1.
- **Backoffice:** nome e cor em `app/Providers/Filament/AdminPanelProvider.php`.
- **Textos do site:** estão nas vistas Blade em `resources/views/`.

## Decisões e limitações conhecidas

- **Sem compilação de CSS/JS:** quem mantiver o site não precisa de Node nem de npm. Se o site crescer muito, pode passar-se para o Vite (que vem com o Laravel).
- **Pesquisa com `LIKE`:** procura o texto tal como está escrito. No MySQL ignora maiúsculas e acentos ("acao" encontra "ação"), mas "propinas" não encontra "propina" (singular). Para algo mais tolerante, o passo seguinte seria um índice `FULLTEXT` do MySQL ou um motor de pesquisa (por exemplo, Laravel Scout).
- **Sem Sanctum:** a API é pública e só de leitura, por isso o `routes/api.php` foi registado à mão em `bootstrap/app.php`, sem o `php artisan install:api` (que instala o Sanctum).
- **Cookies:** o site público usa só os cookies técnicos do Laravel (sessão e proteção CSRF dos formulários). Não há cookies de rastreio.
- **Versão em inglês:** ainda não existe. Os textos da interface estão nas vistas e em `lang/pt/`, e a estrutura não impede acrescentar outra língua.
- **Mensagens de commit:** formato [Conventional Commits](https://www.conventionalcommits.org/), em inglês.

### Questões em aberto

- Que serviços entram na primeira versão, e se há perguntas reais para usar.
- Se é preciso a versão em inglês desde o início.
- A plataforma do site do IPT e a forma de integração (página própria, widget ou CMS).
- As versões de PHP e MySQL no servidor do CIS (o Laravel 13 exige **PHP 8.3+**).
- Onde alojar a prova de conceito.
- O login dos editores: contas próprias ou conta do IPT (LDAP/SSO).
- O guia de identidade visual do IPT.
