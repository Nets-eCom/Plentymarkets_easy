<?php

namespace NetsEasyPay\Providers;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Router;
use Plenty\Plugin\Routing\ApiRouter;

class NetsEasyPayRouteServiceProvider extends RouteServiceProvider
{

    /**
     * @param Router $router
     */
    public function map(Router $router, ApiRouter $apiRouter)
    {
        $apiRouter->version(['v1'], ['namespace' => 'NetsEasyPay\Controllers', 'middleware' => 'oauth'], function ($route) {
            // route for test and debugging 
            $route->get('netseasy/createpayment', 'NetsEasyPayController@CreatePayment');
            $route->get('netseasy/updatepayment/{PaymentId}', 'NetsEasyPayController@UpdatePayment');
            $route->get('netseasy/getpaymentbyid/{PaymentId}', 'NetsEasyPayController@getpaymentbyid');


            $route->get('netseasy/chargepayment/{PaymentId}', 'NetsEasyPayController@ChargePayment');
            $route->get('netseasy/cancelpayment/{PaymentId}', 'NetsEasyPayController@CancelPayment');
            $route->get('netseasy/refundpayment/{PaymentId}', 'NetsEasyPayController@RefundPayment');

            $route->get('netseasy/chargeorderpayment/{orderId}', 'NetsEasyPayController@ChargeOrderPayment');
            $route->get('netseasy/cancelorderpayment/{orderId}', 'NetsEasyPayController@CancelOrderPayment');
            $route->get('netseasy/refundorderpayment/{orderId}', 'NetsEasyPayController@RefundOrderPayment');

            $route->get('netseasy/UpdateNetsEasyPaymentRef/{orderId}/{PaymentId}', 'NetsEasyPayController@UpdateNetsEasyPaymentRef');

            $route->get('netseasy/ChangePlentyPaymentStatus/{PaymentId}/{status}', 'NetsEasyPayController@ChangePlentyPaymentStatus');

            $route->get('netseasy/loadSettings', 'SettingsController@loadSettings');
            $route->get('netseasy/getsession', 'NetsEasyPayController@Get_Session');
            $route->get('netseasy/resetsession', 'NetsEasyPayController@Reset_Session');
            $route->get('netseasy/updateorderproperty/{orderId}/{propId}/{propValue}', 'NetsEasyPayController@UpdateOrderproperty');
            $route->get('netseasy/createplentypayment/{orderId}', 'NetsEasyPayController@CreatePlentyPayment');

            $route->get('netseasy/debug_test', 'NetsEasyPayController@debug_test');
            $route->get('netseasy/test_Notification', 'NetsEasyPayController@test_Notification');
            $route->get('netseasy/getWebstoreConfig', 'NetsEasyPayController@getWebstoreConfig');
        });
    }
}
