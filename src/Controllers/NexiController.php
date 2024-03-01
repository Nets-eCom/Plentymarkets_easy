<?php

namespace NetsEasyPay\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use NetsEasyPay\Helper\SessionHelper;
use NetsEasyPay\Services\NetsEasyPayServiceHttp as NetsEasyPayService;
use NetsEasyPay\Helper\NetsEasyPayHelper;
use NetsEasyPay\Helper\Plenty\Utils;
use NetsEasyPay\Configuration\PluginConfiguration;
use NetsEasyPay\Helper\Plenty\Order\OrderHelper;

class NexiController extends Controller
{

     use \Plenty\Plugin\Log\Loggable;

     

    public function CreatePayment(){
      return NetsEasyPayService::CreatePaymentId();
    }
    public function getpaymentbyid($PaymentId){
      return NetsEasyPayService::getNetsEasyPaymentByID($PaymentId);

    }
    public function UpdatePayment($PaymentId){
      return NetsEasyPayService::UpdatedPaymentId($PaymentId);

    }
    public function ChargePayment($PaymentId){
      return NetsEasyPayService::ChargePayment($PaymentId);
    }
    public function RefundPayment($PaymentId){

      return NetsEasyPayService::RefundPayment($PaymentId);
      
    }
    public function updatenexipaymentRef($orderId,$PaymentId){

      return NetsEasyPayService::UpdateNetsEasyPaymentRef($orderId,$PaymentId);

    }
    public function UpdateNexiPaymentIdprops($PropertyId,$orderId,$PaymentId){

      return NetsEasyPayHelper::UpdateNexiPaymentIdprops($PropertyId,$orderId,$PaymentId);

    }

    public function CancelPayment($PaymentId){

      return NetsEasyPayService::CancelPayment($PaymentId);

    }

    public function ChargeOrderPayment($orderId){

      return NetsEasyPayHelper::ChargeNetsEasyPayment($orderId);

    }

    public function CancelOrderPayment($orderId){

      return NetsEasyPayHelper::CancelNetsEasyPayment($orderId);

    }

    public function RefundOrderPayment($orderId){

      return NetsEasyPayHelper::RefundNetsEasyPayment($orderId);

    }

    public function ChangePlentyPaymentStatus($paymentId,$status){
      
        return NetsEasyPayHelper::ChangePlentyPaymentStatus($paymentId,$status);

    }
    public function CreateInitialProperty($name){
      
      if(!$name)
        return 'a name is required';
      
      return NetsEasyPayHelper::CreateInitialProperty([$name]);
      
    }
    public function CreateInitialOrderProperties(Request $Request){

      $properties = [ $Request->get('name') ?? PluginConfiguration::PAYMENTID_ORDER_PROPERTY ];
                  
      return NetsEasyPayHelper::CreateInitialProperty($properties);
      
    }
    public function Getallprops(Request $Request){
      
      return Utils::GetOrderPropertyType($Request->get('name'));
      
    }

    public function getOrderbyIdWithReferences($orderId){
      
      return OrderHelper::getOrderbyIdWithReferences($orderId);
      
    }
    public function getallcreditnotofOrder($orderId){
      
      return OrderHelper::getallcreditnotofOrder($orderId);
      
    }
    public function createCreditNoteforOrder($orderId,$paymentID,$ChargeId){
      
      return NetsEasyPayHelper::CreateCreditNoteforOrder($orderId,$paymentID,$ChargeId);
      
    }
    

    public function createMopIfNotExists(){
      
      return NetsEasyPayHelper::createMopIfNotExists();
      
    }
    public function Get_Session(){ 

      $basketRepo = pluginApp(BasketRepositoryContract::class);
      $sessionHelper = pluginApp(SessionHelper::class);

      return [

        'EasyPaymentId' => $sessionHelper->getValue('EasyPaymentId'),
        'NexiSelectedMethod' => $sessionHelper->getValue('NexiSelectedMethod'),
        'Basket' => $basketRepo->load()

      ] ;

    }
    public function Reset_Session(){

      

      $basketRepo = pluginApp(BasketRepositoryContract::class);
      $sessionHelper = pluginApp(SessionHelper::class);

      $sessionHelper->setValue('EasyPaymentId', null);
      $sessionHelper->setValue('NexiSelectedMethod', null);

      return [

        'EasyPaymentId' => $sessionHelper->getValue('EasyPaymentId'),
        'NexiSelectedMethod' => $sessionHelper->getValue('NexiSelectedMethod'),
        'Basket' => $basketRepo->load()

      ] ;
      
    }

    public function run_refund_order($creditnoteId){

      return NetsEasyPayHelper::RefundNetsEasyPayment($creditnoteId);
             
      
    }

    public function get_payment_from_credit_note($orderId,$refundId){
      
      return NetsEasyPayHelper::get_payment_from_credit_note($orderId,$refundId);
      
    }

    public function get_payment_by_hash($orderId,$hash){

      return NetsEasyPayHelper::getpaymentByHash($orderId,$hash);

    }

    public function verify_domain(){

      
      return NetsEasyPayHelper::getAppleVerificationText();
       

    }

    
    


    
    
    


    


    
    
    
    

    

}