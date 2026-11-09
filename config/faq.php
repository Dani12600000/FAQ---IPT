<?php

// Definições próprias do FAQ. Os valores vêm do .env; o código lê-os com config('faq.…').

return [

    // Permite a um serviço criar a sua própria cópia de uma pergunta partilhada.
    // Congelada até se decidir se fica, porque reintroduz respostas duplicadas.
    // Desligada, deixa de se poder criar e gerir cópias; as que existem continuam
    // a aparecer no site como perguntas normais.
    'versao_propria' => env('FAQ_VERSAO_PROPRIA', true),

    // Pedidos por minuto e por IP. Os valores são altos porque, na rede do IPT, muitas
    // pessoas saem para a Internet com o mesmo IP.
    'limites' => [
        'api_por_minuto' => (int) env('API_LIMITE_POR_MINUTO', 600),
        'avaliacoes_por_minuto' => (int) env('AVALIACOES_LIMITE_POR_MINUTO', 60),

        // IPs (ou redes, como 10.0.0.0/24) que chamam a API sem limite, por exemplo o
        // servidor do site do IPT. Separados por vírgulas.
        'api_ips_sem_limite' => array_filter(array_map('trim', explode(',', (string) env('API_IPS_SEM_LIMITE', '')))),
    ],

];
