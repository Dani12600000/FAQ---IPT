{{--
    "Esta resposta foi útil?". Sem JavaScript, o formulário é enviado e a página volta
    com o agradecimento. Com JavaScript (public/js/avaliacao.js), responde sem recarregar.
--}}
@props(['pergunta', 'servico' => null])

@php
    $avaliada = session('avaliacao.pergunta') === $pergunta->id;
    $mostrarAjuda = $avaliada && session('avaliacao.util') === false;

    // Ícone do polegar (desenho simples em SVG; o "Não" usa o mesmo, virado ao contrário).
    $polegar = '<svg class="avaliacao-icone" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M7 10v12"/><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"/></svg>';
@endphp

<section @class(['avaliacao', 'avaliacao-respondida' => $avaliada]) aria-labelledby="titulo-avaliacao">
    @unless ($avaliada)
        <form method="post" action="{{ route('faq.avaliar', $pergunta->slug) }}" data-avaliacao>
            @csrf
            <h2 id="titulo-avaliacao">Esta resposta foi útil?</h2>
            <div class="avaliacao-botoes">
                <button type="submit" name="util" value="1">{!! $polegar !!} Sim</button>
                <button type="submit" name="util" value="0" class="avaliacao-nao">{!! $polegar !!} Não</button>
            </div>
        </form>
    @else
        <h2 id="titulo-avaliacao" class="so-leitor">Esta resposta foi útil?</h2>
    @endunless

    <p class="avaliacao-estado" role="status">{{ $avaliada ? session('avaliacao.mensagem') : '' }}</p>

    {{-- Só depois de um "Não". Com JavaScript, é o avaliacao.js que retira o hidden. --}}
    @if ($servico?->temContactos())
        <div class="avaliacao-ajuda" data-avaliacao-ajuda {{ $mostrarAjuda ? '' : 'hidden' }}>
            <p>Precisa de mais ajuda? Contacte <strong>{{ $servico->nome }}</strong>:</p>
            <x-contactos-servico :servico="$servico" />
        </div>
    @endif
</section>
