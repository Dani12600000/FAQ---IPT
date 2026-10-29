<x-layout :titulo="$categoria->nome.' — '.$servico->nome" :descricao="$categoria->descricao ?: 'Perguntas frequentes sobre '.$categoria->nome.'.'">
    <x-breadcrumb :itens="[
        ['label' => $servico->nome, 'url' => route('faq.servico', $servico->slug)],
        ['label' => $categoria->nome, 'url' => null],
    ]" />

    <h1>{{ $categoria->nome }}</h1>
    @if ($categoria->descricao)
        <p class="introducao">{{ $categoria->descricao }}</p>
    @endif

    <section aria-labelledby="titulo-temas">
        <h2 id="titulo-temas">Temas</h2>

        @if ($temas->isEmpty())
            <p>Esta categoria ainda não tem temas publicados.</p>
        @else
            <ul class="cartoes">
                @foreach ($temas as $tema)
                    <li>
                        <a class="cartao" href="{{ route('faq.tema', [$servico->slug, $categoria->slug, $tema->slug]) }}">
                            <span class="cartao-titulo">{{ $tema->nome }}</span>
                            @if ($tema->descricao)
                                <span class="cartao-texto">{{ $tema->descricao }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layout>
