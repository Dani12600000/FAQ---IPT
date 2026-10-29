{{-- Caminho de navegação. Cada item é ['label' => ..., 'url' => ...]; o último é a página atual. --}}
@props(['itens' => []])

@php
    $itens = [['label' => 'Início', 'url' => route('faq.inicio')], ...$itens];
@endphp

<nav class="breadcrumb" aria-label="Caminho de navegação">
    <ol>
        @foreach ($itens as $item)
            <li>
                @if ($loop->last)
                    <span aria-current="page">{{ $item['label'] }}</span>
                @else
                    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
