<?php

namespace NetsEasyPay\Procedures;


use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;
use NetsEasyPay\Helper\NetsEasyPayHelper;
use NetsEasyPay\Helper\Plenty\Notification;
use NetsEasyPay\Helper\Logger;

/**
 * Class RefundEventProcedure
 * @package PaymentMethod\Procedures
 */
class RefundEventProcedure
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

        // check type of order : return / refund
        if($order->typeId == 4  )
            return NetsEasyPayHelper::RefundNetsEasyPayment($order->id);
        

        Logger::error(__FUNCTION__, "NetsEasyPay::ErrorMessages.RefundEventProcedureError","NetsEasyPay::ErrorMessages.RefundEventProcedureErrorMessage",'orderId',$order->id);
        
              
        // show error notification
        $payload = [
            'type' => 'warning',
            'contents' => [
                   'subject' => "NE - Refund Event: OrderId " . $order->id,
                   'body'=> 'NetsEasyPay::ErrorMessages.RefundEventProcedureErrorMessage'
            ]
        ];
    
        return Notification::AddNotification($payload);
        

            
        
        

    }
}