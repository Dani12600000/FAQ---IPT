<x-layout :titulo="$servico->nome" :descricao="$servico->descricao ?: 'Perguntas frequentes sobre '.$servico->nome.'.'">
    <x-breadcrumb :itens="[
        ['label' => $servico->nome, 'url' => null],
    ]" />

    <h1>{{ $servico->nome }}</h1>
    @if ($servico->descricao)
        <p class="introducao">{{ $servico->descricao }}</p>
    @endif

    <section aria-labelledby="titulo-categorias">
        <h2 id="titulo-categorias">Categorias</h2>

        @if ($categorias->isEmpty())
            <p>Este serviço ainda não tem categorias publicadas.</p>
        @else
            <ul class="cartoes">
                @foreach ($categorias as $categoria)
                    <li>
                        <a class="cartao" href="{{ route('faq.categoria', [$servico->slug, $categoria->slug]) }}">
                            <span class="cartao-titulo">{{ $categoria->nome }}</span>
                            @if ($categoria->descricao)
                                <span class="cartao-texto">{{ $categoria->descricao }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layout>
