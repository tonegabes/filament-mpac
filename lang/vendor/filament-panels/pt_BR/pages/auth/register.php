<?php

declare(strict_types=1);

return [
    'title' => 'Cadastrar',

    'heading' => 'Registre-se',

    'actions' => [

        'login' => [
            'before' => 'Já possui uma conta?',
            'label'  => 'Entre aqui',
        ],

    ],

    'form' => [

        'email' => [
            'label' => 'E-mail',
        ],

        'name' => [
            'label' => 'Nome',
        ],

        'password' => [
            'label'                => 'Senha',
            'validation_attribute' => 'password',
        ],

        'password_confirmation' => [
            'label' => 'Confirmar senha',
        ],

        'actions' => [

            'register' => [
                'label' => 'Criar conta',
            ],

        ],

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'Muitas tentativas de cadastro',
            'body'  => 'Por favor, tente novamente em :seconds segundos.',
        ],

    ],

];
