<?php

declare(strict_types=1);

namespace Foodticket\Takeaway\Endpoints;

use Illuminate\Http\Client\RequestException;

trait LogInClient
{
    /**
     * In order to register a POS client with connected restaurants at POS API, post a
     * JSON message to the login endpoint.
     *
     * @throws RequestException
     */
    public function logInClient(
        string $restaurantId,
        string $orderUrl,
        string $aliveUrl,
        string $clientKey,
        ?string $driverUpdateUrl,
        ?string $orderCancelledUrl,
    ) {
        $data = [
            'apiKey' => config('takeaway.api_key'),
            'restaurant' => $restaurantId,
            'orderUrl' => $orderUrl,
            'driverUpdateUrl' => $driverUpdateUrl ?? 'https://nodriverupdates',
            'aliveUrl' => $aliveUrl,
            'version' => config('takeaway.pos_api.version'),
            'clientKey' => $clientKey,
        ];

        if ($orderCancelledUrl) {
            $data['subscribe']['orderCancelled'] = $orderCancelledUrl;
        }

        $response = $this->request()
            ->post(
                "/login",
                array_filter($data)
            );

        if ($response->successful()) {
            return $response->object();
        }

        $response->throw();
    }
}
