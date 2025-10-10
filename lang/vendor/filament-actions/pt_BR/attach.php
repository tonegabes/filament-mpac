<?php

declare(strict_types=1);

return [

    'single' => [

        'label' => 'Vincular',

        'modal' => [

            'heading' => 'Vincular :label',

            'fields' => [

                'record_id' => [
                    'label' => 'Registro',
                ],

            ],

            'actions' => [

                'attach' => [
                    'label' => 'Vincular',
                ],

                'attach_another' => [
                    'label' => 'Salvar e vincular outro',
                ],

            ],

        ],

        'notifications' => [

            'attached' => [
                'title' => 'Vinculado',
            ],

        ],

    ],

];
