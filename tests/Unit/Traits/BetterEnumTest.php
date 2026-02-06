<?php

declare(strict_types=1);

use App\Enums\PageLayouts;

beforeEach(function (): void {
    // BetterEnum is used by PageLayouts; we test the trait through it.
});

it('returns names from cases', function (): void {
    expect(PageLayouts::names())->toBe(['Split', 'Centered', 'FullPage']);
});

it('returns values from cases', function (): void {
    expect(PageLayouts::values())->toBe([
        'layouts.auth.split',
        'layouts.auth.centered',
        'layouts.auth.fullpage',
    ]);
});

it('returns options as name => value', function (): void {
    $options = PageLayouts::options();
    expect($options)->toBeArray()
        ->and($options)->toHaveCount(3)
        ->and(array_keys($options))->toBe(PageLayouts::names())
        ->and(array_values($options))->toBe(PageLayouts::values());
});

it('returns asArray with value keyed by name', function (): void {
    $arr = PageLayouts::asArray();
    expect($arr)->toBeArray()
        ->and($arr['Split'])->toBe('layouts.auth.split')
        ->and($arr['Centered'])->toBe('layouts.auth.centered')
        ->and($arr['FullPage'])->toBe('layouts.auth.fullpage');
});

it('random returns one of the cases', function (): void {
    $cases = PageLayouts::cases();
    for ($i = 0; $i < 10; $i++) {
        $random = PageLayouts::random();
        expect($cases)->toContain($random);
    }
});
