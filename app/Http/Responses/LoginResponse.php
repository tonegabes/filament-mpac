<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as VendorLoginResponse;
use Filament\Facades\Filament;

class LoginResponse implements VendorLoginResponse
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if ($user === null) {
            return redirect()->to(url('/'));
        }

        $currentPanel = Filament::getCurrentPanel();

        if ($currentPanel !== null && $user->canAccessPanel($currentPanel)) {
            return redirect()->intended($currentPanel->getUrl());
        }

        foreach (Filament::getPanels() as $panel) {
            if ($user->canAccessPanel($panel)) {
                return redirect()->intended($panel->getUrl());
            }
        }

        return redirect()->to(url('/'));
    }
}
