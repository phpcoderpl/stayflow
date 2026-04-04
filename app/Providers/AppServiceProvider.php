<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\URL;
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
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        $this->configureMail();
    }

    private function configureMail(): void
    {
        try {
            $host = Setting::get('mail_host');
            if ($host) {
                config([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.host' => $host,
                    'mail.mailers.smtp.port' => Setting::get('mail_port', '587'),
                    'mail.mailers.smtp.username' => Setting::get('mail_username'),
                    'mail.mailers.smtp.password' => Setting::get('mail_password'),
                    'mail.mailers.smtp.encryption' => Setting::get('mail_encryption', 'tls') ?: null,
                    'mail.from.address' => Setting::get('mail_from_address', config('mail.from.address')),
                    'mail.from.name' => Setting::get('mail_from_name', config('mail.from.name')),
                ]);
            }
        } catch (\Throwable) {
            // Table may not exist yet during migrations
        }
    }
}
