<?php

declare(strict_types=1);

namespace Foodticket\Takeaway\Enums;

enum OrderStatus: string
{
    /*
     * This status is set by Takeaway and should not be sent by the restaurant.
     */
    case RECEIVED = 'received';

    /*
     * The order was printed by a restaurant.
     */
    case PRINTED = 'printed';

    /*
     * The order was confirmed with an estimated delivery time.
     */
    case CONFIRMED = 'confirmed';

    /*
     * There was a general error. This status code will trigger our call center
     * agents to call the specific restaurant. An error will not delete the order.
     */
    case ERROR = 'error';

    /*
     * The restaurant started preparing the order.
     */
    case KITCHEN = 'kitchen';

    /*
     * The order is in delivery by a courier.
     */
    case IN_DELIVERY = 'in_delivery';

    /*
     * The order has been delivered by a courier.
     */
    case DELIVERED = 'delivered';
}
