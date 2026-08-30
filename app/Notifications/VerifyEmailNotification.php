<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use ArrayObject;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use LogicException;

final class VerifyEmailNotification extends VerifyEmail
{
    /** @var ArrayObject<string, bool> */
    private ArrayObject $delivery;

    public function __construct()
    {
        $this->delivery = new ArrayObject(['delivered' => false]);
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        if (! $notifiable instanceof User) {
            throw new LogicException('Email verification notifications require a user recipient.');
        }

        return (new MailMessage)
            ->subject(__('auth.verification.mail.subject'))
            ->greeting(__('auth.verification.mail.greeting', ['name' => $notifiable->name]))
            ->line(__('auth.verification.mail.introduction'))
            ->action(__('auth.verification.mail.action'), $this->verificationUrl($notifiable))
            ->line(__('auth.verification.mail.ignore'));
    }

    public function afterSending(mixed $notifiable, string $channel, mixed $response): void
    {
        if ($notifiable instanceof User && $channel === 'mail') {
            $this->delivery['delivered'] = true;
        }
    }

    public function wasDelivered(): bool
    {
        return $this->delivery['delivered'];
    }
}
