<?php

// Traduções que faltam no pacote "pt" do Filament (o resto vem do próprio pacote).

return [

    'column_manager' => [
        'position' => 'Posição :position de :count.',
        'position_in_group' => 'Posição :position de :count em :group.',
        'reordered' => ':column movida para a posição :position de :count.',
        'reordered_deferred' => ':column movida para a posição :position de :count. Aplique as colunas para guardar as alterações.',
        'reordered_in_group' => ':column movida para a posição :position de :count em :group.',
        'reordered_in_group_deferred' => ':column movida para a posição :position de :count em :group. Aplique as colunas para guardar as alterações.',
        'actions' => [
            'move_up' => [
                'label' => 'Mover :column para cima',
            ],
            'move_down' => [
                'label' => 'Mover :column para baixo',
            ],
            'reorder' => [
                'label' => 'Reordenar coluna',
            ],
        ],
    ],

    'columns' => [
        'editable' => [
            'saving' => 'A guardar a alteração...',
            'saved' => 'Alteração guardada.',
            'failure' => 'Não foi possível confirmar a gravação. Atualize a página antes de tentar novamente.',
            'unavailable' => 'Este item já não pode ser editado.',
        ],
        'icon' => [
            'boolean' => [
                'true' => 'Sim',
                'false' => 'Não',
            ],
        ],
        'select' => [
            'no_options_message' => 'Não há opções disponíveis.',
        ],
    ],

    'actions' => [
        'reorder_record' => [
            'label' => 'Reordenar o item :key',
        ],
        'move_record_up' => [
            'label' => 'Mover :label para cima',
        ],
        'move_record_down' => [
            'label' => 'Mover :label para baixo',
        ],
        'toggle_record_content' => [
            'label' => 'Expandir/recolher o item :key',
        ],
    ],

    'loading' => 'A carregar...',

    'reordering' => [
        'position' => ':label, posição :position de :count.',
        'moved' => ':label movido para a posição :position de :count.',
    ],

    'result_count' => '{0} Sem resultados|{1} :count resultado|[2,*] :count resultados',

];
