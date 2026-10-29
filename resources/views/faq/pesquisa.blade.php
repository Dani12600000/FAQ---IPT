<x-layout :titulo="$termo !== '' ? 'Pesquisa: '.$termo : 'Pesquisa'">
    <x-breadcrumb :itens="[
        ['label' => 'Pesquisa', 'url' => null],
    ]" />

    <h1>Pesquisa</h1>

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
                                <a href="{{ route('faq.canonica', $pergunta->slug) }}">{{ $pergunta->pergunta }}</a>
                                <p>{{ Str::limit($pergunta->resposta_texto, 160) }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endfragment
    </x-pesquisa>
</x-layout>
