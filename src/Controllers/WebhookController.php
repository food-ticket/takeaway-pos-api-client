<?php

namespace Foodticket\Takeaway\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Foodticket\Takeaway\TakeawayWebhook;

class WebhookController extends Controller
{
    public function keepAlive(Request $request, string $restaurantId)
    {
        Event::dispatch('takeaway-pos.keep-alive', $restaurantId);

        return response()->noContent(200, ['Content-Type' => 'application/json']);
    }

    public function handle(Request $request, string $event)
    {
        $request->mergeIfMissing(['event' => $event]);

        try {
            $webhook = $this->transformNotification($request);

            Event::dispatch($webhook->eventName(), $webhook);

            return response()->noContent(200, ['Content-Type' => 'application/json']);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return response()->json('Error handling webhook', 500);
        }
    }

    /**
     * @throws Exception
     */
    private function transformNotification(Request $request): TakeawayWebhook
    {
        $notification = $request->all();

        return TakeawayWebhook::fromNotification($notification);
    }
}
