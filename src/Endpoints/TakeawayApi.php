<?php

declare(strict_types=1);

namespace Foodticket\Takeaway\Endpoints;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class TakeawayApi
{
    use LogInClient;
    use LogOutClient;
    use SetOrderStatus;

    public function request(): PendingRequest
    {
        return Http::baseUrl(config('takeaway.api_url'))
            ->asJson();
    }
}
