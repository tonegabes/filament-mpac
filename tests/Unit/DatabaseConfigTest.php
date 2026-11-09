<?php

declare(strict_types=1);

use Illuminate\Support\Env;
use Pdo\Mysql;

it('configures mysql ssl ca with the php 8.5 constant', function (): void {
    $repository = Env::getRepository();
    $original = $repository->get('MYSQL_ATTR_SSL_CA');
    $repository->set('MYSQL_ATTR_SSL_CA', 'C:/certs/ca.pem');

    $deprecations = [];

    set_error_handler(function (int $severity, string $message) use (&$deprecations): bool {
        if ($severity === E_DEPRECATED && str_contains($message, 'MYSQL_ATTR_SSL_CA')) {
            $deprecations[] = $message;

            return true;
        }

        return false;
    });

    try {
        /** @var array{
         *     connections: array{
         *         mysql: array{options: array<int, string>},
         *         mariadb: array{options: array<int, string>}
         *     }
         * } $config
         */
        $config = require config_path('database.php');
    } finally {
        restore_error_handler();

        if (is_string($original)) {
            $repository->set('MYSQL_ATTR_SSL_CA', $original);
        } else {
            $repository->clear('MYSQL_ATTR_SSL_CA');
        }
    }

    expect($deprecations)->toBe([]);

    if (! extension_loaded('pdo_mysql')) {
        expect($config['connections']['mysql']['options'])->toBe([])
            ->and($config['connections']['mariadb']['options'])->toBe([]);

        return;
    }

    expect($config['connections']['mysql']['options'])->toBe([
        Mysql::ATTR_SSL_CA => 'C:/certs/ca.pem',
    ])->and($config['connections']['mariadb']['options'])->toBe([
        Mysql::ATTR_SSL_CA => 'C:/certs/ca.pem',
    ]);
});
