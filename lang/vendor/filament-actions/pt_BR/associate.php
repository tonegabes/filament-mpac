<?php

declare(strict_types=1);

return [

    'single' => [

        'label' => 'Associar',

        'modal' => [

            'heading' => 'Associar :label',

            'fields' => [

                'record_id' => [
                    'label' => 'Registro',
                ],

            ],

            'actions' => [

                'associate' => [
                    'label' => 'Associar',
                ],

                'associate_another' => [
                    'label' => 'Salvar e associar outro',
                ],

            ],

        ],

        'notifications' => [

            'associated' => [
                'title' => 'Associado',
            ],

        ],

    ],

];
