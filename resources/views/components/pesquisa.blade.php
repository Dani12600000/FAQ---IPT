{{--
    Caixa de pesquisa. Sem JavaScript, envia o formulário para /pesquisa.
    Com JavaScript (public/js/pesquisa.js), mostra os resultados enquanto se escreve.
--}}
@props(['termo' => ''])

<form class="pesquisa" role="search" action="{{ route('faq.pesquisa') }}" method="get">
    <label for="q">Pesquisar nas perguntas frequentes</label>
    <div class="pesquisa-linha">
        <input
            type="search"
            id="q"
            name="q"
            value="{{ $termo }}"
            autocomplete="off"
            aria-describedby="q-ajuda"
            aria-controls="resultados-pesquisa"
            data-pesquisa-ao-vivo
        >
        <button type="submit">Pesquisar</button>
    </div>
    <p id="q-ajuda" class="ajuda">Por exemplo: propinas, matrícula, residência.</p>
</form>

{{-- Anuncia o número de resultados aos leitores de ecrã sem ler a lista toda. --}}
<p id="estado-pesquisa" class="so-leitor" aria-live="polite"></p>

<div id="resultados-pesquisa" class="resultados">
    {{ $slot }}
</div>
