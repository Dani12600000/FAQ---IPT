{{-- Contactos de um serviço (só os que estão preenchidos). --}}
@props(['servico'])

<dl class="contactos">
    @if (filled($servico->email))
        <div>
            <dt>Email</dt>
            <dd><a href="mailto:{{ $servico->email }}">{{ $servico->email }}</a></dd>
        </div>
    @endif

    @if (filled($servico->telefone))
        <div>
            <dt>Telefone</dt>
            <dd><a href="tel:{{ preg_replace('/[^+0-9]/', '', $servico->telefone) }}">{{ $servico->telefone }}</a></dd>
        </div>
    @endif

    @if (filled($servico->horario))
        <div>
            <dt>Horário de atendimento</dt>
            <dd>{{ $servico->horario }}</dd>
        </div>
    @endif

    @if (filled($servico->pagina_url))
        <div>
            <dt>Página do serviço</dt>
            <dd><a href="{{ $servico->pagina_url }}">{{ $servico->pagina_url }}</a></dd>
        </div>
    @endif
</dl>
