<?php

declare(strict_types=1);

use App\Enums\NavGroups;

it('returns label equal to value for each nav group', function (NavGroups $group): void {
    expect($group->getLabel())->toBe($group->value);
})->with([
    NavGroups::Authorization,
    NavGroups::Tools,
    NavGroups::Settings,
    NavGroups::Files,
]);

it('returns non-empty icon string for each nav group', function (NavGroups $group): void {
    $icon = $group->getIcon();
    expect($icon)->toBeString()->not->toBeEmpty();
})->with([
    NavGroups::Authorization,
    NavGroups::Tools,
    NavGroups::Settings,
    NavGroups::Files,
]);
