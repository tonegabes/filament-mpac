<?php

declare(strict_types=1);

return [
    'title' => 'Login',

    'heading' => 'Entrar',

    'actions' => [

        'register' => [
            'before' => 'Não possui uma conta?',
            'label'  => 'Registre-se',
        ],

        'request_password_reset' => [
            'label' => 'Esqueceu sua senha?',
        ],

    ],

    'form' => [

        'email' => [
            'label' => 'E-mail',
        ],

        'username' => [
            'label' => 'Usuário MPAC',
        ],

        'password' => [
            'label' => 'Senha',
        ],

        'remember' => [
            'label' => 'Lembre de mim',
        ],

        'actions' => [

            'authenticate' => [
                'label' => 'Entrar',
            ],

        ],

    ],

    'messages' => [

        'failed' => 'Essas credenciais não correspondem aos nossos registros.',

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'Muitas tentativas de login',
            'body'  => 'Por favor tente novamente em :seconds segundos.',
        ],

    ],

];
