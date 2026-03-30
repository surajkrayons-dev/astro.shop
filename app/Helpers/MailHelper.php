<?php

namespace App\Helpers;

class MailHelper
{
    public static function send(string $to, array $data, string $mailer_class): void
    {
        // Configure SMTP Setting
        $settings = getAppSettings();
        if ($settings->isNotEmpty()) {
            $is_settings_found = $settings['smtp_host'] && $settings['smtp_port'] && $settings['smtp_encryption'] && $settings['smtp_username'] && $settings['smtp_password'] && $settings['smtp_username'];

            if ($is_settings_found) {
                config([
                    'mail.mailers.smtp.transport' => 'smtp',
                    'mail.mailers.smtp.host' => $settings['smtp_host'],
                    'mail.mailers.smtp.port' => $settings['smtp_port'],
                    'mail.mailers.smtp.encryption' => $settings['smtp_encryption'],
                    'mail.mailers.smtp.username' => $settings['smtp_username'],
                    'mail.mailers.smtp.password' => $settings['smtp_password'],
                    'mail.from.address' => $settings['smtp_username'],
                    'mail.from.name' => config('app.name'),
                ]);

                \Mail::to($to)->send(new $mailer_class($data));
            }
        }
    }
}
