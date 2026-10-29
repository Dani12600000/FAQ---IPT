{{--
    Página de uma pergunta. Usada com caminho (/{servico}/{categoria}/{tema}/{pergunta})
    e sem caminho (/p/{pergunta}, o URL canónico). Em ambos os casos o canonical aponta para /p/.
--}}
@php
    $urlDaPergunta = fn (array $c) => route('faq.pergunta', [$c['servico']->slug, $c['categoria']->slug, $c['tema']->slug, $pergunta->slug]);
@endphp

<x-layout
    :titulo="$pergunta->pergunta"
    :descricao="Str::limit($pergunta->resposta_texto, 155)"
    :canonico="route('faq.canonica', $pergunta->slug)"
>
    <x-slot:head>
        <script src="{{ asset('js/avaliacao.js') }}" defer></script>
        <x-faq-json-ld :perguntas="collect([$pergunta])" />
    </x-slot:head>

    @if ($caminho)
        <x-breadcrumb :itens="[
            ['label' => $caminho['servico']->nome, 'url' => route('faq.servico', $caminho['servico']->slug)],
            ['label' => $caminho['categoria']->nome, 'url' => route('faq.categoria', [$caminho['servico']->slug, $caminho['categoria']->slug])],
            ['label' => $caminho['tema']->nome, 'url' => route('faq.tema', [$caminho['servico']->slug, $caminho['categoria']->slug, $caminho['tema']->slug])],
            ['label' => $pergunta->pergunta, 'url' => null],
        ]" />
    @else
        <x-breadcrumb :itens="[
            ['label' => $pergunta->pergunta, 'url' => null],
        ]" />
    @endif

    <article class="pergunta">
        <h1>{{ $pergunta->pergunta }}</h1>
        <div class="resposta">
            {!! $pergunta->respostaSegura() !!}
        </div>
    </article>

    <x-avaliacao :pergunta="$pergunta" />

    @if ($outrosCaminhos->isNotEmpty())
        <section class="tambem" aria-labelledby="titulo-tambem">
            <h2 id="titulo-tambem">{{ $caminho ? 'Também aparece em' : 'Aparece em' }}</h2>
            <ul>
                @foreach ($outrosCaminhos as $c)
                    <li>
                        <a href="{{ $urlDaPergunta($c) }}">
                            {{ $c['servico']->nome }} <span aria-hidden="true">›</span><span class="so-leitor">,</span>
                            {{ $c['categoria']->nome }} <span aria-hidden="true">›</span><span class="so-leitor">,</span>
                            {{ $c['tema']->nome }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</x-layout>
