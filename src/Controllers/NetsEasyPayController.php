<?php

namespace NetsEasyPay\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Authorization\Services\AuthHelper;
use NetsEasyPay\Helper\SessionHelper;
use NetsEasyPay\Services\NetsEasyPayServiceHttp as NetsEasyPayService;
use NetsEasyPay\Helper\Plenty\Order\OrderPropertyHelper;
use NetsEasyPay\Helper\Plenty\Order\OrderHelper;
use NetsEasyPay\Helper\NetsEasyPayHelper;
use NetsEasyPay\Helper\Logger;
use NetsEasyPay\Helper\Plenty\Notification;


class NetsEasyPayController extends Controller
{

     use \Plenty\Plugin\Log\Loggable;


    public function CreatePayment()
    {
      return NetsEasyPayService::CreatePaymentId();
    }

    
    public function getpaymentbyid($PaymentId)
    {
      return NetsEasyPayService::getNetsEasyPaymentByID($PaymentId);

    }

    public function UpdatePayment($PaymentId)
    {
      return NetsEasyPayService::UpdatedPaymentId($PaymentId);

    }
    public function ChargePayment($PaymentId)
    {
      return NetsEasyPayService::ChargePayment($PaymentId);
    }
    public function ChargeOrderPayment($orderId){

      return NetsEasyPayHelper::ChargeNetsEasyPayment($orderId);

    }
    public function CancelPayment($PaymentId){

        return NetsEasyPayService::CancelPayment($PaymentId);

    }
    public function CancelOrderPayment($orderId){

      return NetsEasyPayHelper::CancelNetsEasyPayment($orderId);

    }
    public function RefundPayment($PaymentId){

        return NetsEasyPayService::RefundPayment($PaymentId);
        
    }

    public function RefundOrderPayment($orderId){

      return NetsEasyPayHelper::RefundNetsEasyPayment($orderId);

    }

    public function UpdateNetsEasyPaymentRef($orderId,$PaymentId){

      return NetsEasyPayService::UpdateNetsEasyPaymentRef($orderId,$PaymentId);

    }

    public function ChangePlentyPaymentStatus($paymentId,$status){
      
        return NetsEasyPayHelper::ChangePlentyPaymentStatus($paymentId,$status);

    }

    public function Get_Session(){ 

      $basketRepo = pluginApp(BasketRepositoryContract::class);
      $sessionHelper = pluginApp(SessionHelper::class);

      return [

        'EasyPaymentId' => $sessionHelper->getValue('EasyPaymentId'),
        'Basket' => $basketRepo->load()

      ] ;

    }

    public function Reset_Session(){

      

      $basketRepo = pluginApp(BasketRepositoryContract::class);
      $sessionHelper = pluginApp(SessionHelper::class);

      $sessionHelper->setValue('EasyPaymentId', null);

      return [

        'EasyPaymentId' => $sessionHelper->getValue('EasyPaymentId'),
        'Basket' => $basketRepo->load()

      ] ;
      
    }

    public function UpdateOrderproperty($OrderId,$propertyId, $propertyValue){
 
     
     return  OrderPropertyHelper::updateOrCreateValue($OrderId,$propertyId, $propertyValue);
      


    }

    public function CreatePlentyPayment($OrderId,Request $request){
            
          $auth = pluginApp(AuthHelper::class);
          $paymentHelper = pluginApp(NetsEasyPayHelper::class);
          $order = OrderHelper::find($OrderId);
        
          $PaymentType = $request->get('type');
          $EasyPaymentId = $request->get('paymentId');
          $refundId = $request->get('refundId');
          $MopId = $request->get('MopId');
      

          OrderPropertyHelper::updateOrCreateValue($OrderId,3, (string) $MopId);

          $PaymentInfo = [
              'currency' => $order->amounts[0]->currency,
              'amount' => $order->amounts[0]->grossTotal,
              'id' => $refundId ?? $EasyPaymentId,
              'mopId' => $MopId,
              'refundId' => $refundId,
              'paymentId' => $EasyPaymentId,
              'type' => $PaymentType
          ];
          
          Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.CreatePlentyPayment", [                 
            'PaymentInfo' => $PaymentInfo,
        ]);
          $PlentyPayment = $auth->processUnguarded(
                    function () use ($paymentHelper, $PaymentInfo, $OrderId) {
                      return $paymentHelper->CreatePlentyPayment($PaymentInfo,$OrderId);
                }
          );

          Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.CreatePlentyPayment", [                 
              'PlentyPayment' => $PlentyPayment
          ]);

          return $PlentyPayment;

    }

    public function debug_test(){

    
      Logger::debug(__FUNCTION__, 'NetsEasyPay::Debug.DebugDefaultMsg', ['payload' => 'test']);
      
    }

    public function test_Notification(Request $request){
     
      $payload = [
        'type' => $request->get('type'),
        'contents' => [
               'subject' => $request->get('subject'),
               'body'=> $request->get('body')
        ]
     ];

      return Notification::AddNotification($payload);

    }

    public function getWebstoreConfig(){

      return NetsEasyPayHelper::getWebstoreConfig();
      
    }
    

    

}