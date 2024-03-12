<?php

namespace CodeBros\TwoStep\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendVerificationCodeEmail extends Notification implements ShouldQueue
{
    use Queueable;

    protected $code;

    protected $user;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $code)
    {
        $this->code = $code;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Determine which queues should be used for each notification channel.
     *
     * @return array
     */
    public function viaQueues()
    {
        return [
            'mail' => config('laravel-two-step.laravel2stepEmailQueue'),
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $message = new MailMessage();
        $message
            ->from(config('laravel-two-step.verificationEmailFrom'), config('laravel-two-step.verificationEmailFromName'))
            ->subject(trans('laravel-two-step::laravel-verification.verificationEmailSubject'))
            ->greeting(trans('laravel-two-step::laravel-verification.verificationEmailGreeting', ['username' => $this->user->name]))
            ->line(trans('laravel-two-step::laravel-verification.verificationEmailMessage'))
            ->line($this->code)
            ->action(trans('laravel-two-step::laravel-verification.verificationEmailButton'), route('laravel-two-step::verificationNeeded'));

        return $message;
    }
}
