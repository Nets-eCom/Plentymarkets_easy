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
    public function map(ApiRouter $apiRouter,Router $router)
    {
        $apiRouter->version(['v1'], ['namespace' => 'NetsEasyPay\Controllers','middleware' => 'oauth'], function ($route) {
            
            // Route for test and debugging 
            $route->get('nexi/settings', 'SettingsController@getSettings');
            $route->post('nexi/settings/reset_table', 'SettingsController@runMigration');
            
            
            //Test and Debug Nexi Routes
            $route->post('nexi/createpayment', 'NexiController@CreatePayment');
            $route->post('nexi/getpaymentbyid/{PaymentId}', 'NexiController@getpaymentbyid');
            $route->post('nexi/updatepayment/{PaymentId}', 'NexiController@UpdatePayment');
            $route->post('nexi/cancelpayment/{PaymentId}', 'NexiController@CancelPayment');
            $route->post('nexi/chargepayment/{PaymentId}', 'NexiController@ChargePayment');
            $route->post('nexi/refundpayment/{PaymentId}', 'NexiController@RefundPayment');
            $route->post('nexi/methods/create_mop_ids', 'NexiController@createMopIfNotExists');
            $route->post('nexi/property/create_init_props/{name}', 'NexiController@CreateInitialProperty');
            $route->post('nexi/property/create_order_props', 'NexiController@CreateInitialOrderProperties');
            $route->get('nexi/property/all', 'NexiController@Getallprops');
            
            // plenty Order routes
            $route->post('nexi/chargeorderpayment/{orderId}', 'NexiController@ChargeOrderPayment');
            $route->post('nexi/cancelorderpayment/{orderId}', 'NexiController@CancelOrderPayment');
            $route->post('nexi/refundorderpayment/{orderId}', 'NexiController@RefundOrderPayment');
            $route->post('nexi/updatenexipaymentref/{orderId}/{PaymentId}', 'NexiController@updatenexipaymentRef');
            $route->post('nexi/changeplentypaymentstatus/{PaymentId}/{status}', 'NexiController@ChangePlentyPaymentStatus');
            $route->post('nexi/updateorderrefrenceprops/{PropertyId}/{orderId}/{PaymentId}', 'NexiController@UpdateNexiPaymentIdprops');
            $route->get('nexi/orders/{orderId}', 'NexiController@getOrderbyIdWithReferences');
            $route->get('nexi/get_payment_by_hash/{orderId}/{hash}', 'NexiController@get_payment_by_hash');
            
            // Credit Note route
            $route->get('nexi/orders_creditnote/{orderId}', 'NexiController@getallcreditnotofOrder');
            $route->get('nexi/create_creditnote/{orderId}/{paymentID}/{ChargeId}', 'NexiController@createCreditNoteforOrder');
            $route->get('nexi/get_payment_creditnote/{orderId}/{refundId}', 'NexiController@get_payment_from_credit_note');
            $route->get('nexi/refund_creditnote/{creditenoteId}', 'NexiController@run_refund_order');
            
            //Session routes
            $route->get('nexi/get_session', 'NexiController@Get_Session');
            $route->get('nexi/reset_session', 'NexiController@Reset_Session');
            
    
           //WebHooks Routes
            $route->get('nexi/webhooks/get_all_tokens', 'WebHooksController@get_All_Tokens');
            $route->post('nexi/webhooks', 'WebHooksController@subscribe');
            $route->post('nexi/webhooks/generate_new_token', 'WebHooksController@generate_New_token');
            $route->post('nexi/webhooks/run_migration', 'WebHooksController@runMigration');
        });

        $apiRouter->version(['v1'], ['namespace' => 'NetsEasyPay\Controllers'], function ($route) {
            $route->post('nexi/webhooks', 'WebHooksController@subscribe');
        });

        // apple verification link
        $router->get('.well-known/apple-developer-merchantid-domain-association', 'NetsEasyPay\Controllers\NexiController@verify_domain');
        }
}
