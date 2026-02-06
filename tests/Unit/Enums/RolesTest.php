<?php

declare(strict_types=1);

use App\Enums\Roles;

it('returns correct description for each role', function (Roles $role, string $expected): void {
    expect($role->description())->toBe($expected);
})->with([
    [Roles::Developer, 'Full system access with development privileges'],
    [Roles::Admin, 'Administrative access to manage system'],
    [Roles::Operator, 'Operational access to use the system'],
    [Roles::Guest, 'Limited access for viewing only'],
]);

it('identifies admin roles correctly', function (): void {
    expect(Roles::Developer->isAdmin())->toBeTrue()
        ->and(Roles::Admin->isAdmin())->toBeTrue()
        ->and(Roles::Operator->isAdmin())->toBeFalse()
        ->and(Roles::Guest->isAdmin())->toBeFalse();
});

it('identifies developer role correctly', function (): void {
    expect(Roles::Developer->isDeveloper())->toBeTrue()
        ->and(Roles::Admin->isDeveloper())->toBeFalse()
        ->and(Roles::Operator->isDeveloper())->toBeFalse()
        ->and(Roles::Guest->isDeveloper())->toBeFalse();
});

it('returns correct level for each role', function (Roles $role, int $expected): void {
    expect($role->level())->toBe($expected);
})->with([
    [Roles::Developer, 4],
    [Roles::Admin, 3],
    [Roles::Operator, 2],
    [Roles::Guest, 1],
]);
