{{--
    Marcação schema.org FAQPage (https://schema.org/FAQPage) para os motores de pesquisa.
    JSON_HEX_TAG troca "<" e ">" por < e >, para nenhum texto poder fechar o <script>.
--}}
@props(['perguntas'])

@php
    $faq = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $perguntas->map(fn ($pergunta) => [
            '@type' => 'Question',
            'name' => $pergunta->pergunta,
            'url' => route('faq.canonica', $pergunta->slug),
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $pergunta->respostaSegura(),
            ],
        ])->values()->all(),
    ];
@endphp

<script type="application/ld+json">{!! json_encode($faq, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_PRETTY_PRINT) !!}</script>
