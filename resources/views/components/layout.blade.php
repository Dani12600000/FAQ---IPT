{{--
    Layout de todas as páginas públicas.

    - $topo (slot opcional): faixa no topo da página com o caminho, o título e a introdução.
    - $destaque: mostra essa faixa com a cor principal (usado na página inicial).
--}}
@props([
    'titulo',
    'descricao' => 'Respostas às perguntas mais frequentes sobre os serviços do Instituto Politécnico de Tomar.',
    'canonico' => null,
    'destaque' => false,
])
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo }} — Perguntas frequentes do IPT</title>
    <meta name="description" content="{{ $descricao }}">
    @if ($canonico)
        <link rel="canonical" href="{{ $canonico }}">
    @endif
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
    <script src="{{ asset('js/pesquisa.js') }}" defer></script>
    {{ $head ?? '' }}
</head>
<body>
    <a class="saltar" href="#conteudo">Saltar para o conteúdo</a>

    <header class="cabecalho">
        <div class="contentor cabecalho-linha">
            <a class="marca" href="{{ route('faq.inicio') }}">
                <span class="marca-sigla" aria-hidden="true">IPT</span>
                <span class="marca-texto">
                    <span class="marca-nome">Perguntas frequentes</span>
                    <span class="marca-instituicao">Instituto Politécnico de Tomar</span>
                </span>
            </a>
            <a class="cabecalho-pesquisa" href="{{ route('faq.pesquisa') }}">Pesquisar</a>
        </div>
    </header>

    <main id="conteudo" tabindex="-1">
        @isset($topo)
            <div @class(['topo', 'topo-destaque' => $destaque])>
                <div class="contentor">
                    {{ $topo }}
                </div>
            </div>
        @endisset

        <div class="contentor corpo">
            {{ $slot }}
        </div>
    </main>

    <footer class="rodape">
        <div class="contentor">
            <p class="rodape-nome">Instituto Politécnico de Tomar · Perguntas frequentes</p>
            <p>Prova de conceito. O conteúdo atual é apenas de exemplo.</p>
        </div>
    </footer>
</body>
</html>
