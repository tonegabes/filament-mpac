<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Config;

beforeEach(function (): void {
    Config::set('auth.defaults.passwords', 'users');
    Config::set('auth.passwords.users.expire', 60);
});

it('builds mail message with expected subject and body', function (): void {
    $user = User::factory()->make(['email' => 'test@example.com']);
    $notification = new ResetPasswordNotification('token123');
    $notification->url = 'https://example.com/reset?token=token123';

    $message = $notification->toMail($user);

    expect($message)->toBeInstanceOf(Illuminate\Notifications\Messages\MailMessage::class)
        ->and($message->subject)->toBe('Redefinição de senha')
        ->and($message->introLines)->toContain('Você está recebendo este e-mail porque recebemos um pedido de redefinição de senha para sua conta.')
        ->and($message->outroLines)->toContain('Se você não solicitou uma redefinição de senha, nenhuma ação adicional é necessária.');
});

it('includes expire minutes in mail message', function (): void {
    Config::set('auth.passwords.users.expire', 120);
    $user = User::factory()->make();
    $notification = new ResetPasswordNotification('token');
    $notification->url = 'https://example.com/reset';

    $message = $notification->toMail($user);

    expect(implode(' ', $message->outroLines ?? []))->toContain('120');
});
