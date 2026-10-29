@props([
    'titulo',
    'descricao' => 'Respostas às perguntas mais frequentes sobre os serviços do Instituto Politécnico de Tomar.',
    'canonico' => null,
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
        <div class="contentor">
            <a class="marca" href="{{ route('faq.inicio') }}">
                <span class="marca-sigla">IPT</span>
                <span class="marca-nome">Perguntas frequentes</span>
            </a>
        </div>
    </header>

    <main id="conteudo" class="contentor" tabindex="-1">
        {{ $slot }}
    </main>

    <footer class="rodape">
        <div class="contentor">
            <p>Prova de conceito do FAQ do Instituto Politécnico de Tomar. O conteúdo atual é apenas de exemplo.</p>
        </div>
    </footer>
</body>
</html>
