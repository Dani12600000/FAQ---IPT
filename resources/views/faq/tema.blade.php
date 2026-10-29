<x-layout :titulo="$tema->nome.' — '.$categoria->nome" :descricao="$tema->descricao ?: 'Perguntas frequentes sobre '.$tema->nome.'.'">
    <x-breadcrumb :itens="[
        ['label' => $servico->nome, 'url' => route('faq.servico', $servico->slug)],
        ['label' => $categoria->nome, 'url' => route('faq.categoria', [$servico->slug, $categoria->slug])],
        ['label' => $tema->nome, 'url' => null],
    ]" />

    <h1>{{ $tema->nome }}</h1>
    @if ($tema->descricao)
        <p class="introducao">{{ $tema->descricao }}</p>
    @endif

    @if ($perguntas->isEmpty())
        <p>Este tema ainda não tem perguntas publicadas.</p>
    @else
        {{-- Acordeão com <details>: abre e fecha com teclado e leitor de ecrã sem JavaScript. --}}
        <div class="acordeao">
            @foreach ($perguntas as $pergunta)
                <details>
                    <summary>{{ $pergunta->pergunta }}</summary>
                    <div class="resposta">
                        {!! $pergunta->respostaSegura() !!}
                    </div>
                    <p class="ligacao-pergunta">
                        <a href="{{ route('faq.pergunta', [$servico->slug, $categoria->slug, $tema->slug, $pergunta->slug]) }}">
                            Abrir esta pergunta numa página própria<span class="so-leitor">: {{ $pergunta->pergunta }}</span>
                        </a>
                    </p>
                </details>
            @endforeach
        </div>
    @endif
</x-layout>
