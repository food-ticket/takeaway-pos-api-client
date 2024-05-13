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
        ?string $driverUpdateUrl = null,
        ?string $orderCancelledUrl = null,
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

        return $this->request()
            ->post(
                '/login',
                array_filter($data)
            );
    }
}
