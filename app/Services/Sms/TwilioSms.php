<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class TwilioSms
{
    public function send(string $phone, string $message): void
    {
        if (! config('services.twilio.enabled')) {
            Log::info('Twilio SMS skipped because sending is disabled.', [
                'phone' => $phone,
                'message' => $message,
            ]);

            return;
        }

        if (! $this->configured()) {
            Log::info('Twilio SMS skipped because credentials are not configured.', [
                'phone' => $phone,
                'message' => $message,
            ]);

            return;
        }

        $payload = [
            'To' => $this->normalizePhone($phone),
            'Body' => $message,
        ];

        if (filled(config('services.twilio.messaging_service_sid'))) {
            $payload['MessagingServiceSid'] = config('services.twilio.messaging_service_sid');
        } else {
            $payload['From'] = config('services.twilio.from');
        }

        $response = Http::asForm()
            ->withBasicAuth(config('services.twilio.sid'), config('services.twilio.auth_token'))
            ->post(
                'https://api.twilio.com/2010-04-01/Accounts/'.config('services.twilio.sid').'/Messages.json',
                $payload
            );

        if ($response->failed()) {
            Log::warning('Twilio SMS failed.', [
                'phone' => $phone,
                'status' => $response->status(),
                'body' => $response->json() ?: $response->body(),
            ]);

            throw new RuntimeException('Unable to send verification SMS right now.');
        }
    }

    public function configured(): bool
    {
        return (bool) config('services.twilio.enabled')
            && filled(config('services.twilio.sid'))
            && filled(config('services.twilio.auth_token'))
            && (
                filled(config('services.twilio.from'))
                || filled(config('services.twilio.messaging_service_sid'))
            );
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (Str::startsWith($digits, '09')) {
            return '+63'.substr($digits, 1);
        }

        if (Str::startsWith($digits, '63')) {
            return '+'.$digits;
        }

        if (Str::startsWith($phone, '+')) {
            return $phone;
        }

        return '+'.$digits;
    }
}
