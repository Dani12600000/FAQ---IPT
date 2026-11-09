<?php

// Definições próprias do FAQ. Os valores vêm do .env; o código lê-os com config('faq.…').

return [

    // Permite a um serviço criar a sua própria cópia de uma pergunta partilhada.
    // Congelada até se decidir se fica, porque reintroduz respostas duplicadas.
    // Desligada, deixa de se poder criar e gerir cópias; as que existem continuam
    // a aparecer no site como perguntas normais.
    'versao_propria' => env('FAQ_VERSAO_PROPRIA', true),

];
