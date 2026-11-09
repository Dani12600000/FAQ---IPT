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

    <x-slot:topo>
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

        {{-- O título fica na faixa de topo; o id liga-o ao <article> com aria-labelledby. --}}
        <h1 id="titulo-pergunta">{{ $pergunta->pergunta }}</h1>
    </x-slot:topo>

    <div class="pergunta-grelha">
        <div>
            <article class="pergunta" aria-labelledby="titulo-pergunta">
                <div class="resposta">
                    {!! $pergunta->respostaSegura() !!}
                </div>

                @if ($pergunta->ultimaVersao)
                    <p class="pergunta-atualizada">
                        Atualizado em
                        <time datetime="{{ $pergunta->ultimaVersao->created_at->toDateString() }}">{{ $pergunta->ultimaVersao->created_at->format('d/m/Y') }}</time>
                    </p>
                @endif
            </article>

            <x-avaliacao :pergunta="$pergunta" />
        </div>

        @if ($outrosCaminhos->isNotEmpty())
            <aside class="tambem" aria-labelledby="titulo-tambem">
                <h2 id="titulo-tambem">{{ $caminho ? 'Também aparece em' : 'Aparece em' }}</h2>
                <ul>
                    @foreach ($outrosCaminhos as $c)
                        <li>
                            <a href="{{ $urlDaPergunta($c) }}">
                                <span class="tambem-caminho">
                                    {{ $c['servico']->nome }} <span aria-hidden="true">›</span><span class="so-leitor">,</span>
                                    {{ $c['categoria']->nome }} <span aria-hidden="true">›</span><span class="so-leitor">,</span>
                                </span>
                                <span class="tambem-tema">{{ $c['tema']->nome }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </aside>
        @endif
    </div>
</x-layout>
