{{--
    "Esta resposta foi útil?". Sem JavaScript, o formulário é enviado e a página volta
    com o agradecimento. Com JavaScript (public/js/avaliacao.js), responde sem recarregar.
--}}
@props(['pergunta'])

@php
    $avaliada = session('avaliacao.pergunta') === $pergunta->id;
@endphp

<section class="avaliacao" aria-labelledby="titulo-avaliacao">
    @unless ($avaliada)
        <form method="post" action="{{ route('faq.avaliar', $pergunta->slug) }}" data-avaliacao>
            @csrf
            <h2 id="titulo-avaliacao">Esta resposta foi útil?</h2>
            <div class="avaliacao-botoes">
                <button type="submit" name="util" value="1">Sim</button>
                <button type="submit" name="util" value="0">Não</button>
            </div>
        </form>
    @else
        <h2 id="titulo-avaliacao" class="so-leitor">Esta resposta foi útil?</h2>
    @endunless

    <p class="avaliacao-estado" role="status">{{ $avaliada ? session('avaliacao.mensagem') : '' }}</p>
</section>
