<x-layout titulo="Início" destaque>
    <x-slot:topo>
        <x-breadcrumb />

        <h1>Como podemos ajudar?</h1>
        <p class="introducao">Encontre respostas sobre os serviços do Instituto Politécnico de Tomar. Pesquise ou escolha um serviço.</p>

        <x-pesquisa />
    </x-slot:topo>

    <section aria-labelledby="titulo-servicos">
        <h2 id="titulo-servicos">Serviços</h2>

        @if ($servicos->isEmpty())
            <p>Ainda não há serviços publicados.</p>
        @else
            <ul class="cartoes">
                @foreach ($servicos as $servico)
                    <li>
                        <a class="cartao" href="{{ route('faq.servico', $servico->slug) }}">
                            <span class="cartao-titulo">{{ $servico->nome }}</span>
                            @if ($servico->descricao)
                                <span class="cartao-texto">{{ $servico->descricao }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layout>
