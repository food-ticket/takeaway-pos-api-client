<?php

declare(strict_types=1);

namespace Foodticket\Takeaway\Endpoints;

use Illuminate\Http\Client\RequestException;

trait LogOutClient
{
    /**
     * Restaurants that are logged out no longer receive orders.
     *
     * If there are multiple restaurants connected to the same health check
     * endpoint (aliveUrl) it will continue to be pinged until all those
     * restaurants are logged out.
     *
     * @throws RequestException
     */
    public function logOutClient(
        string $restaurantId,
    ) {
        $data = [
            'apiKey' => config('services.takeaway.api_key'),
            'restaurant' => $restaurantId,
        ];

        return $this->request()
            ->post(
                '/logout',
                array_filter($data)
            );
    }
}
