<x-layout :titulo="$termo !== '' ? 'Pesquisa: '.$termo : 'Pesquisa'">
    <x-slot:topo>
        <x-breadcrumb :itens="[
            ['label' => 'Pesquisa', 'url' => null],
        ]" />

        <h1>Pesquisa</h1>
    </x-slot:topo>

    <x-pesquisa :termo="$termo">
        {{-- Este bloco é também devolvido sozinho à pesquisa enquanto se escreve. --}}
        @fragment('resultados')
            <div data-total="{{ $resultados->count() }}">
                @if ($termo === '')
                    <p>Escreva o que procura na caixa acima.</p>
                @elseif (mb_strlen($termo) < $minimo)
                    <p>Escreva pelo menos {{ $minimo }} caracteres.</p>
                @elseif ($resultados->isEmpty())
                    <p>Não encontrámos perguntas com «{{ $termo }}». Experimente outras palavras ou <a href="{{ route('faq.inicio') }}">escolha um serviço</a>.</p>
                @else
                    <p class="contagem">
                        {{ $resultados->count() === 1 ? '1 resultado' : $resultados->count().' resultados' }} para «{{ $termo }}»
                    </p>
                    <ul class="lista-resultados">
                        @foreach ($resultados as $pergunta)
                            <li>
                                <a class="resultado" href="{{ route('faq.canonica', $pergunta->slug) }}">
                                    <span class="resultado-titulo">{{ $pergunta->pergunta }}</span>
                                    <span class="resultado-texto">{{ Str::limit($pergunta->resposta_texto, 160) }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endfragment
    </x-pesquisa>
</x-layout>
