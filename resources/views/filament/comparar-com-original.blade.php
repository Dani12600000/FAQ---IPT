{{--
    Modal "Comparar com a original" do backoffice: a base comum (o texto da original quando
    as duas eram iguais), a original agora e a versão própria, lado a lado.
--}}
<div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(16rem, 1fr));">
    @foreach ($colunas as $titulo => $texto)
        <section>
            <h3 style="font-weight: 600; margin: 0 0 .5rem;">{{ $titulo }}</h3>

            @if ($texto)
                @include('filament.pre-visualizacao-pergunta', $texto)
            @else
                <p style="font-style: italic;">Esta versão já não existe no histórico.</p>
            @endif
        </section>
    @endforeach
</div>
