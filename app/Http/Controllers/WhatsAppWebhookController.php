<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Verify the webhook (GET request).
     */
    public function verify(Request $request)
    {
        $verifyToken = config('services.whatsapp.webhook_verify_token');

        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    /**
     * Handle incoming webhook events (POST request).
     */
    public function handle(Request $request)
    {
        // Log the incoming payload for now to verify connectivity
        Log::info('WhatsApp Webhook Received:', $request->all());

        return response('EVENT_RECEIVED', 200);
    }
}
