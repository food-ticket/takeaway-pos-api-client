<?php

declare(strict_types=1);

namespace Foodticket\Takeaway\Endpoints;

use Carbon\Carbon;
use Illuminate\Http\Client\RequestException;

trait SetOrderStatus
{
    /**
     * The change_status endpoint is used by the client to adjust the status of an order.
     *
     * It is mandatory to send a “confirmed” status for every order. When sending a
     * “confirmed” status it is also mandatory to include a changedDeliveryTime field with
     * the expected delivery time.
     *
     * @throws RequestException
     */
    public function setOrderStatus(
        string $statusUrl,
        string $id,
        string $status,
        string $key,
        ?Carbon $changedDeliveryTime = null,
        ?string $message = null,
    ) {
        $data = [
            'id' => $id,
            'status' => $status,
            'key' => $key,
        ];

        if ($changedDeliveryTime) {
            $data['changedDeliveryTime'] = $changedDeliveryTime->toIso8601String();
        }

        if ($message) {
            $data['text'] = $message;
        }

        $response = $this->request()
            ->post(
                $statusUrl,
                array_filter($data)
            );

        if ($response->successful()) {
            return $response->object();
        }

        $response->throw();
    }
}
