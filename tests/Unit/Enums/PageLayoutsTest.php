<?php

declare(strict_types=1);

use App\Enums\PageLayouts;

it('returns correct label for each layout', function (PageLayouts $layout, string $expected): void {
    expect($layout->getLabel())->toBe($expected);
})->with([
    [PageLayouts::Split, 'Dividido'],
    [PageLayouts::Centered, 'Centralizado'],
    [PageLayouts::FullPage, 'Tela inteira'],
]);

it('returns non-empty icon string for each layout', function (PageLayouts $layout): void {
    expect($layout->getIcon())->toBeString()->not->toBeEmpty();
})->with([
    PageLayouts::Split,
    PageLayouts::Centered,
    PageLayouts::FullPage,
]);

it('returns correct description for each layout', function (PageLayouts $layout, string $expected): void {
    expect($layout->getDescription())->toBe($expected);
})->with([
    [PageLayouts::Split, 'Divide a página em duas colunas'],
    [PageLayouts::Centered, 'Centraliza o conteúdo na página'],
    [PageLayouts::FullPage, 'Ocupa a tela inteira'],
]);

it('returns extra text equal to value for each layout', function (PageLayouts $layout): void {
    expect($layout->getExtraText())->toBe($layout->value);
})->with([
    PageLayouts::Split,
    PageLayouts::Centered,
    PageLayouts::FullPage,
]);

it('provides BetterEnum names', function (): void {
    $names = PageLayouts::names();
    expect($names)->toBeArray()
        ->and($names)->toContain('Split', 'Centered', 'FullPage')
        ->and($names)->toHaveCount(3);
});

it('provides BetterEnum values', function (): void {
    $values = PageLayouts::values();
    expect($values)->toBeArray()
        ->and($values)->toContain('layouts.auth.split', 'layouts.auth.centered', 'layouts.auth.fullpage')
        ->and($values)->toHaveCount(3);
});

it('provides BetterEnum options', function (): void {
    $options = PageLayouts::options();
    expect($options)->toBeArray()
        ->and($options)->toHaveKeys(['Split', 'Centered', 'FullPage'])
        ->and($options['Split'])->toBe('layouts.auth.split');
});

it('provides BetterEnum asArray', function (): void {
    $arr = PageLayouts::asArray();
    expect($arr)->toBeArray()
        ->and($arr)->toHaveKeys(['Split', 'Centered', 'FullPage'])
        ->and($arr['FullPage'])->toBe('layouts.auth.fullpage');
});

it('returns a valid case from random', function (): void {
    $random = PageLayouts::random();
    expect($random)->toBeInstanceOf(PageLayouts::class)
        ->and(PageLayouts::cases())->toContain($random);
});
