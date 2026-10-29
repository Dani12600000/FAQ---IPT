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
- Commits pequenos e com mensagens claras.
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

## Primeiro passo sugerido

1. Verificar PHP e Composer.
2. Criar o projeto Laravel.
3. Ligar ao `faq_ipt`.
4. Criar as migrações e os modelos com as relações.
5. Correr `php artisan migrate`.
