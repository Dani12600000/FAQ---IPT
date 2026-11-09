<x-layout :titulo="$servico->nome" :descricao="$servico->descricao ?: 'Perguntas frequentes sobre '.$servico->nome.'.'">
    <x-slot:topo>
        <x-breadcrumb :itens="[
            ['label' => $servico->nome, 'url' => null],
        ]" />

        <h1>{{ $servico->nome }}</h1>
        @if ($servico->descricao)
            <p class="introducao">{{ $servico->descricao }}</p>
        @endif
    </x-slot:topo>

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

    @if ($servico->temContactos())
        <section class="servico-contactos" aria-labelledby="titulo-contactos">
            <h2 id="titulo-contactos">Contactos</h2>
            <x-contactos-servico :servico="$servico" />
        </section>
    @endif
</x-layout>
