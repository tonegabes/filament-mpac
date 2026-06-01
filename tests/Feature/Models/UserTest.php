<?php

declare(strict_types=1);

use App\Models\User;

it('denies panel access when no panel permission can be resolved', function (): void {
    $user = User::factory()->make();

    expect($user->canAccessPanel(null))->toBeFalse();
});
