<?php

declare(strict_types=1);

use App\Http\Responses\LoginResponse;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\Request;

afterEach(function (): void {
    Mockery::close();
});

it('redirects to home when user cannot access panel', function (): void {
    $user = Mockery::mock(User::class);
    $user->shouldReceive('canAccessPanel')->once()->with(null)->andReturn(false);

    $request = Request::create('/login', 'GET');
    $request->setUserResolver(fn () => $user);

    Filament::shouldReceive('getCurrentPanel')->once()->andReturn(null);

    $response = (new LoginResponse)->toResponse($request);

    expect($response->getTargetUrl())->toBe(url('/'));
});

it('redirects intended to default panel url when user can access panel', function (): void {
    $panel = Mockery::mock(\Filament\Panel::class);
    $panel->shouldReceive('getUrl')->andReturn('/admin');
    $panel->shouldReceive('getId')->andReturn('admin');

    $user = Mockery::mock(User::class);
    $user->shouldReceive('canAccessPanel')->once()->with(Mockery::on(fn ($p) => $p === $panel))->andReturn(true);

    $request = Request::create('/login', 'GET');
    $request->setUserResolver(fn () => $user);

    Filament::shouldReceive('getCurrentPanel')->once()->andReturn($panel);
    Filament::shouldReceive('getDefaultPanel')->once()->andReturn($panel);

    $response = (new LoginResponse)->toResponse($request);

    expect($response->getTargetUrl())->toContain('/admin');
});
