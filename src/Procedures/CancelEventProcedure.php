<?php

namespace NetsEasyPay\Procedures;

use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;
use NetsEasyPay\Helper\NetsEasyPayHelper;

/**
 * Class CancelEventProcedure
 * @package PaymentMethod\Procedures
 */
class CancelEventProcedure
{
    /**
     * @param EventProceduresTriggered $eventProceduresTriggered
     */
    public function run(
        EventProceduresTriggered $event
    )
    {
        /**
         * Get current order the event is triggered from.
         *
         * @var Order $order
         */
        $order = $event->getOrder();

        NetsEasyPayHelper::CancelNetsEasyPayment($order->id);

        
    }
}