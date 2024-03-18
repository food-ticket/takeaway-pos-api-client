<?php

return [
    /*
    |------------------------------------------------------------
    | Takeaway POS API configuration
    |------------------------------------------------------------
    |
    | Set the endpoints root URL, API key and (optionally) tweak
    | other settings for routing incoming webhooks.
    |
    */

    'api_url' => env('TAKEAWAY_POS_API_URL', 'https://posapi.takeaway.com/'),

    'api_key' => env('TAKEAWAY_POS_API_KEY'),

    'api_secret' => env('TAKEAWAY_POS_API_SECRET'),

    'routes_prefix' => '/takeaway-pos/webhooks',

    'routes_middleware' => ['webhooks'],
];
