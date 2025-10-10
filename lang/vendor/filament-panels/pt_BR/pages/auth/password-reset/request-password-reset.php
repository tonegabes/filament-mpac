<?php

declare(strict_types=1);

return [
    'title' => 'Redefina sua senha',

    'heading' => 'Esqueceu sua senha?',

    'description' => 'Digite seu e-mail e enviaremos um link para redefinir sua senha.',

    'actions' => [

        'login' => [
            'before' => 'Mudou de ideia?',
            'label'  => 'voltar ao login',
        ],

    ],

    'form' => [

        'email' => [
            'label' => 'E-mail',
        ],

        'actions' => [

            'request' => [
                'label' => 'Enviar email',
            ],

        ],

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'Muitas solicitações',
            'body'  => 'Por favor tente novamente em :seconds segundos.',
        ],

    ],

];
