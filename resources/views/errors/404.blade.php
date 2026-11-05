<x-layout titulo="Página não encontrada">
    <x-slot:topo>
        <x-breadcrumb :itens="[
            ['label' => 'Página não encontrada', 'url' => null],
        ]" />

        <h1>Página não encontrada</h1>
        <p class="introducao">O endereço pode estar errado ou a página pode já não estar disponível. Experimente pesquisar ou <a href="{{ route('faq.inicio') }}">volte ao início</a>.</p>
    </x-slot:topo>

    <x-pesquisa />
</x-layout>
