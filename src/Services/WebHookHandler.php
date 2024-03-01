<?php

namespace NetsEasyPay\Services;

use NetsEasyPay\Models\AccessToken;
use NetsEasyPay\Services\NetsEasyPayServiceHttp as NetsEasyPayService;
use NetsEasyPay\Services\SettingsService;
use NetsEasyPay\Helper\NetsEasyPayHelper;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Payment\Models\Payment;
use NetsEasyPay\Helper\Plenty\Order\OrderHelper;
use NetsEasyPay\Helper\Logger;

class WebHookHandler 
{


  public static function HandleChargeCreated($data)
  {
    
    $response =  NetsEasyPayHelper::CreateChargePayment($data,Payment::STATUS_APPROVED);

    if($response){
      $Settings =  SettingsService::getAllSetting();
      $allowedOrderStatusChangeChargeEvent = $Settings['allowedOrderStatusChangeChargeEvent'];
  
      //change order status.
      if($allowedOrderStatusChangeChargeEvent)
         OrderHelper::setOrderStatusId($response['orderId'],$Settings['ChargeCompletedStatus'] );
    }

    return $response;

  }
  public static function HandleChargeFaild($data)
  {

     $response =  NetsEasyPayHelper::CreateChargePayment($data,Payment::STATUS_REFUSED,1);

     if($response){
       $Settings =  SettingsService::getAllSetting();
       $allowedOrderStatusChangeChargeEvent = $Settings['allowedOrderStatusChangeChargeEvent'];
   
       //change order status.
       if($allowedOrderStatusChangeChargeEvent)
          OrderHelper::setOrderStatusId($response['orderId'],$Settings['ChargeFaildStatus'] );
     }
 
     return $response;

  }

  public static function HandleCancelCreated($data)
  {
    $EasyPaymentId = $data['paymentId'];

    // get reference
    $NetsEasyPayment = NetsEasyPayService::getNetsEasyPaymentByID($EasyPaymentId);

    if(!$NetsEasyPayment){
      Logger::error(__FUNCTION__, "NetsEasyPay::Debug.ApiError", ['NetsPayment' => $EasyPaymentId]);
      return null;
    }
          
    $orderId = $NetsEasyPayment["payment"]["orderDetails"]["reference"];

     //change order status to cancelled.
     $Settings =  SettingsService::getAllSetting();
     $allowedOrderStatusChangeCancelEvent = $Settings['allowedOrderStatusChangeCancelEvent'];
     
     if($allowedOrderStatusChangeCancelEvent)
        OrderHelper::setOrderStatusId($orderId,$Settings['CancelCompletedStatus'] );
     
     
     return $EasyPaymentId;
  }
  public static function HandleCancelfailed($data)
  { 
    // log an error that you can't cancel this order payment
    $EasyPaymentId = $data['paymentId'];

    // get reference
    $NetsEasyPayment = NetsEasyPayService::getNetsEasyPaymentByID($EasyPaymentId);

    if(!$NetsEasyPayment){
      Logger::error(__FUNCTION__, "NetsEasyPay::Debug.ApiError", ['NetsPayment' => $EasyPaymentId]);
      return null;
    }
          
    $orderId = $NetsEasyPayment["payment"]["orderDetails"]["reference"];

     
     $Settings =  SettingsService::getAllSetting();
     $allowedOrderStatusChangeCancelEvent = $Settings['allowedOrderStatusChangeCancelEvent'];

     //change order status to cancelled.
     if($allowedOrderStatusChangeCancelEvent)
        OrderHelper::setOrderStatusId($orderId,$Settings['CancelFaildStatus'] );
    

  
    return $EasyPaymentId;
  }
  
  public static function HandleRefundInitiated_v2($data){
    
    return 'HandleRefundInitiated_v2';
  }

  public static function HandleRefundInitiated($data)
  {

    $EasyPaymentId = $data['paymentId'];
    
    $details = self::getDetailsFromNetspayment($EasyPaymentId);
    
    if(!$details)
       return false;

    $orderId = $details['orderId'];
    $chargeId = $data['chargeId'];
    $refundId = $data['refundId'];
    $amount = $data['amount']["amount"];
  

    $NetsPlentyPayment = NetsEasyPayHelper::get_payment_from_credit_note($orderId,$refundId);


    Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.HandleRefundInitiatedPayload", [
        'orderId' => $orderId ,
        'chargeId' => $chargeId ,
        'refundId' => $refundId ,
        'amount' => $amount ,
        'NetsPlentyPayment' => $NetsPlentyPayment ,
    ],'orderId',$orderId);
   

    if(!$NetsPlentyPayment){

      $Settings =  SettingsService::getAllSetting();
      $allowCreditNoteCreationOnRefund = $Settings['allowCreditNoteCreationOnRefund'];

      if($allowCreditNoteCreationOnRefund){

          $creditNoteCreationStatus = $Settings['creditNoteCreationStatus'];
          // create credit note using $orderId $EasyPaymentId $chargeId
          $creditNote = NetsEasyPayHelper::CreateCreditNoteforOrder($orderId,$EasyPaymentId,$chargeId,$creditNoteCreationStatus);
          
          if($creditNote){
                //create payment for this credit not using  $refundId 
                foreach ($creditNote->properties as $key => $property) {
                  if($property->typeId == 3){
                    $MopId = $property->value;
                  }
                }
              $PaymentInfo = [
                                'currency' => $creditNote->amounts[0]->currency,
                                'amount' => $amount/100,
                                'id' => $refundId,
                                'status' => Payment::STATUS_AWAITING_APPROVAL,
                                'mopId' => $MopId,
                                'reference' => $EasyPaymentId,
                                'chargeId' => $chargeId,
                                'refundId' => $refundId,
                                'type' => 'debit',
                                'unaccountable' => true
              ];

              $plentyPayment = NetsEasyPayHelper::CreatePlentyPayment($PaymentInfo, $creditNote->id);

              return true;
          }
          
          // show error credit not can't be created
          
          Logger::error(__FUNCTION__, "NetsEasyPay::ErrorMessages.CreateCreditNoteError", [
            'refundId' => $refundId,
          ],'orderId',$orderId);

          return true;
      }

      
      Logger::error(__FUNCTION__, "NetsEasyPay::ErrorMessages.CreateCreditNotFound", [
          'refundId' => $refundId,
      ],'orderId',$orderId);

    
    }

    return true;
    

  }
  public static function HandleRefundCompleted($data)
  {

    $response =  self::changeRefundPaymentStatus($data,Payment::STATUS_APPROVED);

    if($response){
      $Settings =  SettingsService::getAllSetting();
      $allowedOrderStatusChangeRefundEvent = $Settings['allowedOrderStatusChangeRefundEvent'];
  
      //change order status.
      if($allowedOrderStatusChangeRefundEvent)
         OrderHelper::setOrderStatusId($response['orderId'],$Settings['RefundCompletedStatus'] );
    }

    return $response;
   
  }
  public static function HandleRefundFailed($data)
  {

    $response =  self::changeRefundPaymentStatus($data,Payment::STATUS_REFUSED);

    if($response){
      $Settings =  SettingsService::getAllSetting();
      $allowedOrderStatusChangeRefundEvent = $Settings['allowedOrderStatusChangeRefundEvent'];
  
      //change order status.
      if($allowedOrderStatusChangeRefundEvent)
         OrderHelper::setOrderStatusId($response['orderId'],$Settings['RefundFaildStatus'] );
    }

    return $response;
  }


  public static function getDetailsFromNetspayment($EasyPaymentId){

    $NetsEasyPayment = NetsEasyPayService::getNetsEasyPaymentByID($EasyPaymentId);

    if(!$NetsEasyPayment){
      Logger::error(__FUNCTION__, "NetsEasyPay::Debug.ApiError", ['NetsPayment' => $EasyPaymentId]);
      return null;
    }
          
    $orderId = $NetsEasyPayment["payment"]["orderDetails"]["reference"];

    $Order = pluginApp(OrderRepositoryContract::class)->findById($orderId);

    if(!$Order){
      Logger::error(__FUNCTION__, "NetsEasyPay::ErrorMessages.NoOrderFound", ['orderId' => $orderId]);
      return null;
    }

    return [
      'order' => $Order,
      'orderId' => $orderId,
      'NetsEasyPayment' => $NetsEasyPayment
    ];
  }
  public static function changeRefundPaymentStatus($data,$status){

    $EasyPaymentId  = $data['paymentId'];
    

    $details = self::getDetailsFromNetspayment($EasyPaymentId);
    
    if(!$details)
      return null;
    
        

    $orderId = $details['orderId'];
    $refundId = $data['refundId'];
    
    $NetsPlentyPayment = NetsEasyPayHelper::get_payment_from_credit_note($orderId,$refundId);

    if(!$NetsPlentyPayment){
     
      Logger::error(__FUNCTION__, "NetsEasyPay::ErrorMessages.NoPaymentFound", [
        'NetsPayment' => $EasyPaymentId,
        'refundId' => $refundId,
      ]);

      return null;
    }

    // update payment status to refunded or partialy reunded
    $plentPaymentUpdated = NetsEasyPayHelper::ChangePlentyPaymentStatus($NetsPlentyPayment->id,$status);

    return [
             'payment' => $plentPaymentUpdated,
             'orderId' => $orderId
           ];
  }
  public static function generate_New_token()
  {

          $tokens = AccessToken::where('status', '=', 1);
              
          foreach ($tokens as $key => $token) {
              AccessToken::createOrupdate([
                                            'id'=>$token->id,
                                            'status' => 0
                                          ]);
          }
          
          $token_value =self::GenerateToken_Value();
          
          $token = AccessToken::createOrupdate(['token_value' => $token_value,'status' => 1]);
          
          return $token;

  }
  public static function GenerateToken_Value($length = 45) {
       
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }


    $token = base64_encode($randomString);

    return  $token;
  
  }
   
}




