<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\Middleware\RateLimited;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $phone,
        protected string $message,
    ) {
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new RateLimited('whatsapp')];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $phoneNumberId = config('services.whatsapp.phone_number_id');
        $accessToken = config('services.whatsapp.access_token');
        $version = 'v22.0';

        if (empty($phoneNumberId) || empty($accessToken)) {
            Log::error("WhatsApp Cloud API credentials missing. Message to {$this->phone} failed.");
            $this->fail(new \Exception("Missing WhatsApp credentials"));
            return;
        }

        try {
            Log::info("Sending WhatsApp Cloud API message to {$this->phone}");

            $url = "https://graph.facebook.com/{$version}/{$phoneNumberId}/messages";

            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $this->phone,
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => $this->message
                ]
            ];

            $response = Http::withToken($accessToken)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            if ($response->successful()) {
                Log::info("WhatsApp message sent successfully to {$this->phone}. ID: " . $response->json('messages.0.id'));
            } else {
                Log::error("WhatsApp Cloud API Error: " . $response->body());
                // Throw exception to trigger retry
                throw new \Exception("WhatsApp API Error: " . $response->body());
            }

        } catch (\Throwable $e) {
            Log::error("WhatsApp send failed for {$this->phone}: " . $e->getMessage());

            if ($this->attempts() < 3) {
                 $this->release(60);
            } else {
                 $this->fail($e);
            }
        }
    }
}
