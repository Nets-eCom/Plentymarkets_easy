<?php

namespace NetsEasyPay\Services;


use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Contracts\BasketItemRepositoryContract;
use NetsEasyPay\Helper\Plenty\Variation\VariationHelper;
use Plenty\Modules\Authorization\Services\AuthHelper;
use NetsEasyPay\Helper\NetsEasyPayHelper;
use NetsEasyPay\Helper\AddressHelper;
use NetsEasyPay\Helper\Logger;

use NetsEasyPay\Services\SettingsService;


class NetsEasyPayServiceCurl 
{


  public static function CreatePaymentId()
  {
      $Settings =  SettingsService::getAllSetting($withsecretKey = true);
      $Basket = pluginApp(BasketRepositoryContract::class)->load();
      $BasketItem = pluginApp(BasketItemRepositoryContract::class)->all();
           
      $Paymentdata = self::CreatePaymentData();

      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => self::getApiUrl($Settings['UseTestCredentials'])."/payments",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($Paymentdata),
        CURLOPT_HTTPHEADER => array(
          'Accept: application/json',
          'Authorization: '.$Settings['secretKey'],
          'Content-type: application/json',
        ),
      ));

      $response = curl_exec($curl);
      $error = curl_error($curl);
    
      curl_close($curl);
      
      if ($error) {
        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiError",  $error);
        return null;
      } 

      $decoded_response = json_decode($response,true);
      $paymentId = $decoded_response['paymentId'];
      
      Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.CreatedPaymentId", $paymentId);

      if($Basket->shippingAmount > 0)
           return self::UpdatedPaymentId($paymentId);
      
      
      return $paymentId;


  }
  public static function getNetsEasyPaymentByID($PaymentId) {

    $Settings =  SettingsService::getAllSetting($withsecretKey = true);
  
    $curl = curl_init();
    curl_setopt_array($curl, [
      CURLOPT_URL => self::getApiUrl($Settings['UseTestCredentials'])."/payments/".$PaymentId,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => [
        'Authorization: '.$Settings['secretKey'],
        "CommercePlatformTag: plentymarkets"
      ],
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);
    
    curl_close($curl);


    if ($error) {

      Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiError",  $error);
      return null;

    } 
    
    Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiResponse",[
      'response' => $response
    ]);

    return json_decode($response,true);

  }

  public static function UpdatedPaymentId($PaymentId){
      
      $Settings =  SettingsService::getAllSetting($withsecretKey = true);
      $Paymentdata = self::CreatePaymentData($withShipping = true);

      $updatedData = [
        'amount' => $Paymentdata['order']['amount'],
        'items' => $Paymentdata['order']['items'],
      ];
      
      if($Paymentdata['order']['shipping']){
        $updatedData['shipping'] = $Paymentdata['order']['shipping'];
      }

      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => self::getApiUrl($Settings['UseTestCredentials'])."/payments/".$PaymentId.'/orderitems',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_POSTFIELDS => json_encode($updatedData),
        CURLOPT_HTTPHEADER => array(
          'Accept: application/json',
          'Authorization: '.$Settings['secretKey'],
          'Content-type: application/json',
        ),
      ));

      $response = curl_exec($curl);
      $error = curl_error($curl);

      curl_close($curl);
      
      if ($error) {
        
        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiError",  $error);
        
        return null ;

      } 
      
      Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiResponse", [
        'paymentId'=> $PaymentId,
        'payload' => $updatedData,
        'response' => $response
      ]);

      return $PaymentId;
      
   
  }

  public static function ChargePayment($PaymentId){

        $Settings =  SettingsService::getAllSetting($withsecretKey = true);     
        $NetsEasyPayment = self::getNetsEasyPaymentByID($PaymentId);
             
        if(!$NetsEasyPayment)
            return false;

        $data = [
          "amount" => $NetsEasyPayment["payment"]["summary"]["reservedAmount"]
        ];
        
        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ChargePaymentPayload",[
          'NetsEasyPayment' => $NetsEasyPayment ,
          'Payload' => $data,
        ]);
        $curl = curl_init();

        curl_setopt_array($curl, [
          CURLOPT_URL => self::getApiUrl($Settings['UseTestCredentials'])."/payments/".$PaymentId."/charges",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => json_encode($data),
          CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Authorization: '.$Settings['secretKey'],
            'Content-type: application/json',
          ],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
          
          Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiError",  $error);
          return ['error' => json_decode($error,true)] ;

        } 

        $charge_response = json_decode($response,true);
        
        if(!$charge_response['chargeId']){
          
          Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiError",  $charge_response['message']);
           return [
                    'error' => [
                         'msg' => $charge_response['message'],
                         'code' =>$charge_response['code']
                      ] 
                  ] ;
        }

        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiResponse", $response);

        return $charge_response;
  } 
  public static function CancelPayment($PaymentId){
          
         $Settings =  SettingsService::getAllSetting($withsecretKey = true);
         $NetsEasyPayment = self::getNetsEasyPaymentByID($PaymentId);
         
         if(!$NetsEasyPayment)
              return false;
         $data = [
            "amount" => $NetsEasyPayment["payment"]["summary"]["reservedAmount"]
         ];

         $curl = curl_init();

          curl_setopt_array($curl, [
            CURLOPT_URL => self::getApiUrl($Settings['UseTestCredentials'])."/payments/".$PaymentId."/cancels",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
              'Accept: application/json',
              'Authorization: '.$Settings['secretKey'],
              'Content-type: application/json',
            ],
          ]);

          $response = curl_exec($curl);
          $error = curl_error($curl);

          curl_close($curl);


          if ($error) {
          
            Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiError",  $error);
            return ['error' => $error] ;
  
          } 
        
           Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiResponse", $response);
  
         return [
                  'response' => $response
                ];
  }
  public static function RefundPayment($PaymentId,$orderItemData=[],$amount=0){
    
        $Settings =  SettingsService::getAllSetting($withsecretKey = true);
        $NetsEasyPayment = self::getNetsEasyPaymentByID($PaymentId);
        
        if(!$NetsEasyPayment)
             return false;
        
        $charges = $NetsEasyPayment["payment"]["charges"];
       
        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.RefundPaymentPayload",[
          'charges' => $charges ,
          'amount' => $amount,
          'orderItemData' => $orderItemData
        ]);

        $correspond_charge = null;

        if(empty($orderItemData) &&  $amount == 0){ // full refund test
          $correspond_charge = $charges[0]; 
          $amount =  $charges[0]['amount'];
        }else{
            //get chargeId correspond to orderItemData
            foreach ($charges as $key => $charge) {
                    
                $flag = false;

                foreach ($orderItemData as $key => $item) {
                    if(!self::is_charge_Contains_item($item['reference'],$charge['orderItems'])){
                        $flag = false;
                        break;
                    }
                    $flag = true;
                }

                if($flag){
                    $correspond_charge = $charge;
                    break;
                }
            }
        }

        $chargeId = $correspond_charge['chargeId'];
        
        if(!$chargeId){
          return [
                  'error' => [
                      'msg' => 'no charge found correspond to the selected items',
                   ] 
                  ];
        }
        $data = [
            "amount" => $amount,
            "orderItems" => $orderItemData
        ];
       
        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.RefundPaymentPayload",[
          'charge' => $chargeId ,
          'Payload' => $data,
        ]);

        $curl = curl_init();

        curl_setopt_array($curl, [
          CURLOPT_URL => self::getApiUrl($Settings['UseTestCredentials'])."/charges/".$chargeId."/refunds",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => json_encode($data),
          CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Authorization: '.$Settings['secretKey'],
            'Content-type: application/json',
          ],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
          
          Logger::debug(__FUNCTION__,"NetsEasyPay::Debug.ApiError",  $error);
          return ['error' => $error] ;

        } 

        $refund_response = json_decode($response,true);

        if(!$refund_response['refundId']){
          
          Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiError",  $refund_response);
           return [
                    'error' => [
                         'msg' => $refund_response['message'],
                         'code' =>$refund_response['code']
                      ] 
                  ] ;
        }

        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiResponse",  $refund_response);

        return  $refund_response;

  }
  public static function UpdateNetsEasyPaymentRef($OrderId,$PaymentId){
      
      $Settings =  SettingsService::getAllSetting($withsecretKey = true);
      
      $updatedReference = [
        'checkoutUrl' => NetsEasyPayHelper::getDomain(),
        'reference' => $OrderId,
      ];
      $curl = curl_init();

      curl_setopt_array($curl, array( 
        CURLOPT_URL => self::getApiUrl($Settings['UseTestCredentials'])."/payments/".$PaymentId.'/referenceinformation',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_POSTFIELDS => json_encode($updatedReference),
        CURLOPT_HTTPHEADER => array(
          'Accept: application/json',
          'Authorization: '.$Settings['secretKey'],
          'Content-type: application/json',
        ),
      ));

      $response = curl_exec($curl);
      $error = curl_error($curl);

      curl_close($curl);
      
      
      
      if ($error) {
        
        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiError", [
          'error' => $error,
        ]);

        return ['error' => $error] ;
      } 
      
      Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiResponse", $updatedReference);

      return ['response' => $response];
  }

  public static function  CreatePaymentData($withShipping = false){


        $Settings =  SettingsService::getAllSetting($withsecretKey = true);
        
        $Paymentdata = [
                          'checkout' => [
                                "integrationType" => "EmbeddedCheckout",
                                "url" =>  NetsEasyPayHelper::getDomain(),
                                "termsUrl"=> NetsEasyPayHelper::getDomain(),
                                "merchantHandlesConsumerData"=> $Settings['merchanthandlesconsumerdata'],
                                "charge"=> false,
                                "merchantHandlesShippingCost" => true
                          ],
        ];

        $Basket = pluginApp(BasketRepositoryContract::class)->load();
        $BasketItem = pluginApp(BasketItemRepositoryContract::class)->all();
        $auth = pluginApp(AuthHelper::class);

        $shippingAmount  = $Basket->shippingAmount ?? 0;
        $Paymentdata['order']['amount'] = ($Basket->basketAmount - $shippingAmount) * 100 ;
        $Paymentdata['order']['currency'] = $Basket->currency;
        $Paymentdata['order']['reference'] = $Basket->id .'-'. date('m-d-y-h:s');
        
        $billingAddress = $auth->processUnguarded(function () use ($Basket) {
          return   pluginApp(AddressHelper::class)->getBasketBillingAddress($Basket);
        });

        $shippingAddress = $auth->processUnguarded(function () use ($Basket) {
            return pluginApp(AddressHelper::class)->getBasketShippingAddress($Basket);
        });

        if($shippingAddress['email']){
            $Paymentdata['checkout']['consumer']['email'] = $shippingAddress['email'];
        }
        
        if($shippingAddress['phoneNumber']){
           $Paymentdata['checkout']['consumer']['phoneNumber']  = $shippingAddress['phoneNumber'];
        }
        
        if($shippingAddress['company']){
           $Paymentdata['checkout']['consumer']['company']  = $shippingAddress['company'];
        }
        
        if($shippingAddress['privatePerson']){
          $Paymentdata['checkout']['consumer']['privatePerson']  = $shippingAddress['privatePerson'];
        }

        $Paymentdata['checkout']['consumer']['shippingAddress']  = $shippingAddress['address'];
        
        if(!empty($billingAddress)){

          $Paymentdata['checkout']['shipping']['enableBillingAddress'] = true;
          $Paymentdata['checkout']['consumer']['billingAddress']  = $billingAddress['address'];
       
        }

        foreach ($BasketItem as $key => $item) {
            
          $Variation = VariationHelper::findbyIds([$item->variationId]);

          $taxRate =  $item->vat;
          $price = $item->price * 100;
          $netPrice = round($item->price / (1+($taxRate/100)) * 100);
          $grossTotal = $item->quantity * $price;
          //$netTotalAmount = round($grossTotal * 100/(100+$taxRate));
          // calculate netTotal based on calculated netPrice in order to gurantee validation (netAmount = unitprice * quantity)
          $netTotalAmount = $item->quantity * $netPrice;
          $taxamount = $grossTotal - $netTotalAmount;
           

          $Paymentdata['order']['items'][] = [
                                                "reference" => $item->variationId,
                                                "name" => $Variation['variationTexts'][0]['name'],
                                                "quantity" => $item->quantity,
                                                "unit"=> "pcs",
                                                "unitPrice" => $netPrice,
                                                "taxRate"=> $taxRate*100,
                                                "taxAmount"=> $taxamount,
                                                "grossTotalAmount" => $grossTotal,
                                                "netTotalAmount" => $netTotalAmount
                                             ]; 


        }

        if($withShipping && $Basket->shippingAmount > 0){
          
          $taxRate = round(($Basket->shippingAmount / $Basket->shippingAmountNet) -1,2) * 100;
          $grossTotalAmount = $Basket->shippingAmount * 100;
          $netTotalAmount = $Basket->shippingAmountNet * 100;
          $taxamount = $grossTotalAmount - $netTotalAmount;

          $Paymentdata['order']['items'][] = [
                                                "reference"=>"Shipping",
                                                "name"=> "Shipping",
                                                "quantity" => 1,
                                                "unit" => "NA",
                                                "unitPrice"=> $netTotalAmount, 
                                                "taxRate"=> $taxRate * 100,
                                                "taxAmount"=> $taxamount ,
                                                "netTotalAmount"=> $netTotalAmount,
                                                "grossTotalAmount" => $grossTotalAmount 
                                              ];

          $Paymentdata['order']['shipping'] = [
                                                  "costSpecified" => true
                                              ];
          $Paymentdata['order']['amount'] = $Paymentdata['order']['amount'] + $grossTotalAmount;

        }

        if($Basket->couponCode){
                
                  $amount = $Basket->couponDiscount*100;
                  $Paymentdata['order']['items'][] = [
                    "reference" => $Basket->couponCode,
                    "name" => 'Coupon Code',
                    "quantity" => 1,
                    "unit"=> "pcs",
                    "unitPrice" => $amount ,
                    "grossTotalAmount" => $amount,
                    "netTotalAmount" => $amount
                ]; 
        }
        
        $logMsg = $withShipping ? 'UpdatePaymentViaApi' : 'CreatePaymentViaApi';
        
        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.".$logMsg, [
            //'Basket' => $Basket ,
            //'BasketItem' => $BasketItem,
            'Payload' => $Paymentdata,
            //'PriceData' => $item
            //'billingAddress' => $billingAddress,
            //'shippingAddress' => $shippingAddress,    
        ]);
          
       
       
        return $Paymentdata;

  }
  public static function is_charge_Contains_item($itemId,$chargeItems){
      
      $found = false;
      foreach ($chargeItems as $key => $item) {
        if((string) $itemId == (string) $item['reference']){
          $found = true;
        }
      }
      return $found;
  }

  public static function getApiUrl($Testmode){

      $url = 'https://api.dibspayment.eu/v1';
      
      if($Testmode)
          $url = 'https://test.api.dibspayment.eu/v1';

      return $url;
 
  }
   
}




