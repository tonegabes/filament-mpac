<?php

declare(strict_types=1);

use App\Http\Responses\LoginResponse;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

it('redirects to home when user cannot access panel', function (): void {
    $user = new class (false) extends User
    {
        public function __construct(private readonly bool $canAccessPanel = false)
        {
            parent::__construct();
        }

        public function canAccessPanel(?Panel $panel): bool
        {
            return $this->canAccessPanel;
        }
    };

    $request = Request::create('/login', 'GET');
    $request->setUserResolver(fn () => $user);

    Filament::shouldReceive('getCurrentPanel')->once()->andReturn(null);
    Filament::shouldReceive('getPanels')->once()->andReturn([]);

    $response = (new LoginResponse)->toResponse($request);

    if (! $response instanceof RedirectResponse) {
        $this->fail('Expected a redirect response.');
    }

    expect($response->getTargetUrl())->toBe(url('/'));
});

it('redirects intended to current panel url when user can access current panel', function (): void {
    $panel = Panel::make()
        ->id('admin')
        ->path('admin');

    $user = new class (true) extends User
    {
        public function __construct(private readonly bool $canAccessPanel = false)
        {
            parent::__construct();
        }

        public function canAccessPanel(?Panel $panel): bool
        {
            return $this->canAccessPanel;
        }
    };

    $request = Request::create('/login', 'GET');
    $request->setUserResolver(fn () => $user);

    Filament::shouldReceive('getCurrentPanel')->once()->andReturn($panel);

    $response = (new LoginResponse)->toResponse($request);

    if (! $response instanceof RedirectResponse) {
        $this->fail('Expected a redirect response.');
    }

    expect($response->getTargetUrl())->toContain('/admin');
});

it('redirects to first accessible panel when user cannot access current panel', function (): void {
    $appPanel = Panel::make()->id('app')->path('');
    $adminPanel = Panel::make()->id('admin')->path('admin');

    $user = new class extends User
    {
        public function canAccessPanel(?Panel $panel): bool
        {
            return $panel?->getId() === 'admin';
        }
    };

    $request = Request::create('/login', 'GET');
    $request->setUserResolver(fn () => $user);

    Filament::shouldReceive('getCurrentPanel')->once()->andReturn($appPanel);
    Filament::shouldReceive('getPanels')->once()->andReturn(['app' => $appPanel, 'admin' => $adminPanel]);

    $response = (new LoginResponse)->toResponse($request);

    if (! $response instanceof RedirectResponse) {
        $this->fail('Expected a redirect response.');
    }

    expect($response->getTargetUrl())->toContain('/admin');
});
