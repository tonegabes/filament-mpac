<?php

declare(strict_types=1);

namespace App\Notifications;

use Filament\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * Get the reset password notification mail message for the given URL.
     *
     * @param  string  $url
     * @return MailMessage
     */
    protected function buildMailMessage($url)
    {
        $count = Config::string('auth.passwords.' . Config::string('auth.defaults.passwords') . '.expire');

        return (new MailMessage)
            ->subject('Redefinição de senha')
            ->line('Você está recebendo este e-mail porque recebemos um pedido de redefinição de senha para sua conta.')
            ->action('Redefinir senha', $url)
            ->line("Este link de redefinição de senha expirará em {$count} minutos.")
            ->line('Se você não solicitou uma redefinição de senha, nenhuma ação adicional é necessária.');
    }
}
