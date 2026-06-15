# Takeaway POS API implementation for Laravel

[![GitHub license](https://img.shields.io/github/license/Naereen/StrapDown.js.svg)](https://github.com/Naereen/StrapDown.js/blob/master/LICENSE)

This package allows you to easily make requests to the Takeaway POS API and handle incoming webhooks.

## Requirements

- PHP >= 8.3
- Laravel >= 12.0

## Installation

```bash
composer require foodticket/takeaway-pos-api-client
```

The package will automatically register itself via Laravel's package discovery.

Publish the config file:

```bash
php artisan vendor:publish --tag=config
```

## Configuration

Add the following variables to your `.env` file:

| Variable | Default | Description |
|---|---|---|
| `TAKEAWAY_POS_API_URL` | `https://posapi.takeaway.com/` | Base URL for the POS API |
| `TAKEAWAY_POS_API_KEY` | — | Your POS API key |
| `TAKEAWAY_POS_API_SECRET` | — | Your POS API secret |
| `TAKEAWAY_POS_API_VERSION` | `1.3` | API version sent on login |

The published `config/takeaway.php` also exposes two routing options:

```php
'routes_prefix'     => '/takeaway-pos/webhooks',
'routes_middleware' => ['webhooks'],
```

Change these to control where incoming webhook routes are mounted and which middleware they run under.

## Usage

Instantiate `TakeawayApi` directly or resolve it from the container:

```php
use Foodticket\Takeaway\Endpoints\TakeawayApi;

$api = new TakeawayApi();
```

---

## Outgoing API endpoints

### Login — `logInClient()`

Register a POS client with its connected restaurant at the Takeaway POS API. Must be called before the restaurant can receive orders.

```php
$api->logInClient(
    restaurantId: 'restaurant-uuid',
    orderUrl: 'https://your-app.example/takeaway-pos/webhooks/order',
    aliveUrl: 'https://your-app.example/takeaway-pos/webhooks/keep-alive/restaurant-uuid',
    clientKey: 'your-client-key',
    driverUpdateUrl: null,       // optional — defaults to 'https://nodriverupdates' if omitted
    orderCancelledUrl: null,     // optional — subscribes to orderCancelled events
);
```

**HTTP request:** `POST /login`

| Field | Required | Description |
|---|---|---|
| `apiKey` | yes | Pulled from `takeaway.api_key` config |
| `restaurant` | yes | The restaurant identifier |
| `orderUrl` | yes | URL called by Takeaway when a new order arrives |
| `aliveUrl` | yes | URL polled by Takeaway to check POS availability |
| `clientKey` | yes | Unique key identifying this POS client |
| `version` | yes | Pulled from `takeaway.api_version` config |
| `driverUpdateUrl` | no | URL for driver status updates |
| `subscribe.orderCancelled` | no | URL called when an order is cancelled |

---

### Logout — `logOutClient()`

Deregister a restaurant from the POS API. Logged-out restaurants no longer receive orders. If multiple restaurants share the same `aliveUrl`, the health-check ping continues until all of them are logged out.

```php
$api->logOutClient(
    restaurantId: 'restaurant-uuid',
);
```

**HTTP request:** `POST /logout`

| Field | Required | Description |
|---|---|---|
| `apiKey` | yes | Pulled from `takeaway.api_key` config |
| `restaurant` | yes | The restaurant identifier |

---

### Set order status — `setOrderStatus()`

Update the status of an order. A `confirmed` status **must** be sent for every order, and it **must** include `changedDeliveryTime`.

```php
use Foodticket\Takeaway\Enums\OrderStatus;
use Carbon\Carbon;

$api->setOrderStatus(
    statusUrl: $statusUrl,           // URL provided in the incoming order payload
    id: $orderId,
    status: OrderStatus::CONFIRMED,
    key: $orderKey,
    changedDeliveryTime: Carbon::now()->addMinutes(30), // required when status is CONFIRMED
    message: null,                   // optional — free-text note
);
```

**HTTP request:** `POST {statusUrl}`

| Field | Required | Description |
|---|---|---|
| `id` | yes | Order identifier |
| `status` | yes | One of the `OrderStatus` enum values (see below) |
| `key` | yes | Order key provided in the incoming order payload |
| `changedDeliveryTime` | conditional | ISO 8601 timestamp — **required** when status is `confirmed` |
| `text` | no | Optional message/note |

#### `OrderStatus` enum values

| Case | Value | Description |
|---|---|---|
| `OrderStatus::RECEIVED` | `received` | Set by Takeaway — do not send this yourself |
| `OrderStatus::PRINTED` | `printed` | Order was printed by the restaurant |
| `OrderStatus::CONFIRMED` | `confirmed` | Order confirmed with estimated delivery time |
| `OrderStatus::ERROR` | `error` | General error — triggers a call-centre callback; does not delete the order |
| `OrderStatus::KITCHEN` | `kitchen` | Restaurant started preparing the order |
| `OrderStatus::IN_DELIVERY` | `in_delivery` | Order picked up by a courier |
| `OrderStatus::DELIVERED` | `delivered` | Order delivered to the customer |

---

## Incoming webhooks

The package automatically registers two routes under the configured prefix (default `/takeaway-pos/webhooks`):

| Method | Path | Description |
|---|---|---|
| `GET` | `/takeaway-pos/webhooks/keep-alive/{restaurantId}` | Health-check endpoint polled by Takeaway |
| `ANY` | `/takeaway-pos/webhooks/{event}` | Generic handler for all other webhook events |

### Keep-alive

Takeaway polls the `aliveUrl` you registered on login to verify the POS is online. The route fires a Laravel event and returns HTTP 200:

```
Event::dispatch('takeaway-pos.keep-alive', $restaurantId);
```

Listen for it in your application:

```php
Event::listen('takeaway-pos.keep-alive', function (string $restaurantId) {
    // mark restaurant as online, etc.
});
```

### Event webhooks

All other incoming notifications (orders, cancellations, driver updates, etc.) are routed through the `/{event}` handler. The payload must include an `event` field; the controller fires a Laravel event named:

```
takeaway-pos.{kebab-slug-of-event-name}
```

For example, an `orderCreated` notification fires `takeaway-pos.order-created`.

The event payload is a `TakeawayWebhook` object:

```php
Event::listen('takeaway-pos.order-created', function (TakeawayWebhook $webhook) {
    $webhook->eventName();    // 'takeaway-pos.order-created'
    $webhook->restaurantId(); // restaurant identifier from the payload
    $webhook->resourceId();   // order id from the payload
    $webhook->payload();      // full raw payload array
});
```

---

## Security Vulnerabilities

If you discover a security vulnerability within this project, please report it by email to [developer@foodticket.nl](mailto:developer@foodticket.nl).
