<?php

namespace App\Providers;

use App\Support\Words;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('admin', fn ($user) => $user->active && $user->role === 'admin');
        Gate::define('moderate', fn ($user) => $user->active && in_array($user->role, ['admin', 'moderator'], true));
        VerifyEmail::toMailUsing(function ($user, $url) {
            $t = fn ($key) => Words::get($key, $user->locale);

            return (new MailMessage)->subject($t('verify'))->view('emails.account', ['title' => $t('verify'), 'body' => $t('mail.verify'), 'ignore' => $t('mail.ignore'), 'url' => $url, 'locale' => $user->locale]);
        });
        ResetPassword::toMailUsing(function ($user, $token) {
            $t = fn ($key) => Words::get($key, $user->locale);
            $url = route('password.reset', ['token' => $token, 'email' => $user->email, 'lang' => $user->locale]);

            return (new MailMessage)->subject($t('reset'))->view('emails.account', ['title' => $t('reset'), 'body' => $t('mail.reset'), 'ignore' => $t('mail.ignore'), 'url' => $url, 'locale' => $user->locale]);
        });
    }
}
