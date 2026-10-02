<?php

namespace App\Notifications;

use App\Support\Words;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountInvitation extends Notification
{
    public function __construct(public string $token, public string $language) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $t = fn ($key) => Words::get($key, $this->language);

        return (new MailMessage)->subject($t('invite.accept'))->view('emails.account', [
            'title' => $t('invite.accept'), 'body' => $t('invite.mail'), 'ignore' => $t('mail.ignore'),
            'url' => route('invitation.show', ['token' => $this->token, 'lang' => $this->language]), 'locale' => $this->language,
        ]);
    }
}
