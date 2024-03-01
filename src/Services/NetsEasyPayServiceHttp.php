<?php

namespace NetsEasyPay\Services;


use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Contracts\BasketItemRepositoryContract;
use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;
use Plenty\Modules\Frontend\Contracts\Checkout;
use NetsEasyPay\Helper\Plenty\Variation\VariationHelper;
use Plenty\Modules\Authorization\Services\AuthHelper;
use IO\Extensions\Constants\ShopUrls;
use NetsEasyPay\Models\AccessToken;
use NetsEasyPay\Helper\NetsEasyPayHelper;
use NetsEasyPay\Helper\AddressHelper;
use NetsEasyPay\Helper\Logger;
use NetsEasyPay\Configuration\PluginConfiguration;
use NetsEasyPay\Services\SettingsService;


class NetsEasyPayServiceHttp 
{


  public static function CreatePaymentId()
  {
      $Settings =  SettingsService::getAllSetting($withsecretKey = true);
      $LibraryCall = pluginApp(LibraryCallContract::class);
      $Basket = pluginApp(BasketRepositoryContract::class)->load();
      $BasketItem = pluginApp(BasketItemRepositoryContract::class)->all();

      $Paymentdata = self::CreatePaymentData();
      
      $data =   [
                  'Payload' => [
                    'Paymentdata' => $Paymentdata
                  ],
                  'Method'   => 'CreatePaymentId',
                  'Token'    => $Settings['secretKey'],
                  'TestMode' => $Settings['UseTestCredentials']  
                ];

      Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.CreatePaymentPayload", $data );  
     
      $response = $LibraryCall->call( 'NetsEasyPay::GuzzleHttp',$data);

      if ($response['error']) {
        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiError",  $response);
        return null;
      } 

      $paymentId = $response['data']['paymentId'];
      
      if($Basket->shippingAmount > 0)
           return self::UpdatedPaymentId($paymentId);
      
      
      return $paymentId;


  }
  public static function getNetsEasyPaymentByID($PaymentId) {

    $Settings =  SettingsService::getAllSetting($withsecretKey = true);
    $LibraryCall = pluginApp(LibraryCallContract::class);
    $data = [
              'Payload' => [
                'PaymentId' => $PaymentId
              ],
              'Method'  => 'getNetsEasyPaymentByID',
              'Token'   => $Settings['secretKey'],
              'TestMode' => $Settings['UseTestCredentials']  
            ];

    Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.RetrievePaymentDetails", $data );
    
    $response = $LibraryCall->call( 'NetsEasyPay::GuzzleHttp',$data);
    


    if ($response['error']) {

      Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiError",$response);
      return null;

    } 
    
    Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiResponse",$response);

    return $response['data'];

  }
  public static function UpdatedPaymentId($PaymentId){
      
      $Settings =  SettingsService::getAllSetting($withsecretKey = true);
      $LibraryCall = pluginApp(LibraryCallContract::class);
      $Paymentdata = self::CreatePaymentData($withShipping = true);

      $updatedData = [
        'amount' => $Paymentdata['order']['amount'],
        'items' => $Paymentdata['order']['items'],
      ];
      
      if($Paymentdata['order']['shipping']){
        $updatedData['shipping'] = $Paymentdata['order']['shipping'];
      }
      $data = [
                'Payload' => [
                  'PaymentId'=>$PaymentId,
                  'updatedData' => $updatedData
                ],
                'Method'  => 'UpdatedPaymentId',
                'Token'   => $Settings['secretKey'],
                'TestMode' => $Settings['UseTestCredentials']  
              ];

      Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.UpdatePaymentPayload", $data );
    
      $response = $LibraryCall->call( 'NetsEasyPay::GuzzleHttp',$data);

      if ($response['error']) {
        
        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiError", $response);
        
        return null ;

      } 
      
      Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiResponse",$response);

      return $PaymentId;
      
   
  }
  public static function ChargePayment($PaymentId){

        $Settings =  SettingsService::getAllSetting($withsecretKey = true);
        $LibraryCall = pluginApp(LibraryCallContract::class);
        $NetsEasyPayment = self::getNetsEasyPaymentByID($PaymentId);
             
        if(!$NetsEasyPayment)
             return ['error' => 'Can Not Get PaymentId'];

        $ChargeData = [
          "amount" => $NetsEasyPayment["payment"]["summary"]["reservedAmount"]
        ];
        
        $data = [
                    'Payload' => [
                      'ChargeData' => $ChargeData,
                      'PaymentId' => $PaymentId
                    ],
                    'Method'  => 'ChargePayment',
                    'Token'   => $Settings['secretKey'],
                    'TestMode' => $Settings['UseTestCredentials']  
                ];

        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ChargePaymentPayload", $data );  
        
        $response = $LibraryCall->call( 'NetsEasyPay::GuzzleHttp',$data);
      

        if ($response['error']) {
          
          Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiError",  $response);
          
          return $response;

        } 
        
        if(!$response['data']['chargeId']){
          
          Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiError",  $response['data']['message']);
           return [
                    'error' => [
                         'msg' => $response['data']['message'],
                         'code' =>$response['data']['code']
                      ] 
                  ] ;
        }

        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiResponse", $response);
        
        return $response['data'];
        
  } 
  public static function CancelPayment($PaymentId){
          
         $Settings =  SettingsService::getAllSetting($withsecretKey = true);
         $LibraryCall = pluginApp(LibraryCallContract::class);
         $NetsEasyPayment = self::getNetsEasyPaymentByID($PaymentId);
         
         if(!$NetsEasyPayment){
          Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiError",  $NetsEasyPayment);
          
          return ['error' => 'Can Not Get PaymentId'];
         }
              
         
        $CancelData = [
            "amount" => $NetsEasyPayment["payment"]["summary"]["reservedAmount"]
         ];

        $data = [
                    'Payload' => [
                      'CancelData' => $CancelData,
                      'PaymentId' => $PaymentId
                    ],
                    'Method'  => 'CancelPayment',
                    'Token'   => $Settings['secretKey'],
                    'TestMode' => $Settings['UseTestCredentials']  
        ];

        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.CancelPaymentPayload", $data );

        $response = $LibraryCall->call( 'NetsEasyPay::GuzzleHttp',$data);
      
       
        if ($response['error']) {
          
            Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiError",  $response);
            return $response ;
  
        } 
        
        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiResponse", $response);
        
         return $response;
  }
  public static function RefundPayment($PaymentId,$orderItemData=[],$amount=0){
    
        $Settings =  SettingsService::getAllSetting($withsecretKey = true);
        $LibraryCall = pluginApp(LibraryCallContract::class);
        $NetsEasyPayment = self::getNetsEasyPaymentByID($PaymentId);
        
        if(!$NetsEasyPayment)
             return ['error' => 'Can Not Get PaymentId'];
        
        $charges = $NetsEasyPayment["payment"]["charges"];
        
        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.RefundPaymentChargesList",$charges);

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

        $RefundData = [
            "amount" => $amount,
            "orderItems" => $orderItemData
        ];
        
        $data = [
                  'Payload' => [
                    'chargeId' => $chargeId,
                    'RefundData' => $RefundData
                  ],
                  'Method'  => 'RefundPayment',
                  'Token'   => $Settings['secretKey'],
                  'TestMode' => $Settings['UseTestCredentials']  
        ];

        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.RefundPaymentPayload", $data );

        $response = $LibraryCall->call('NetsEasyPay::GuzzleHttp',$data);

        if ($response['error']) {
          
          Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiError",  $response);
          
          return $response ;

        } 

        if(!$response['data']['refundId']){
          
          Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiError",  $response);
           return [
                    'error' => [
                         'msg' => $response['data']['message'],
                         'code' =>$response['data']['code']
                      ] 
                  ] ;
        }

        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiResponse",  $response);

        return  [
                   'data' => $response['data'],
                   'chargeId' => $chargeId,
                ];

  }
  public static function UpdateNetsEasyPaymentRef($OrderId,$PaymentId){
      
      $Settings =  SettingsService::getAllSetting($withsecretKey = true);
      $LibraryCall = pluginApp(LibraryCallContract::class);
      
      $updatedReference = [
        'checkoutUrl' => NetsEasyPayHelper::getDomain(),
        'reference' => $OrderId,
      ];
      
      $data = [
                'Payload' => [
                  'PaymentId' => $PaymentId,
                  'ReferenceData' => $updatedReference
                ],
                'Method'  => 'UpdateNetsEasyPaymentRef',
                'Token'   => $Settings['secretKey'],
                'TestMode' => $Settings['UseTestCredentials']  
              ];

      Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.UpdatePaymentReferencePayload", $data );
    
      $response = $LibraryCall->call( 'NetsEasyPay::GuzzleHttp',$data);
        
      
      if ($response['error']) {
        
        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiError", $response);

        return $response ;
      } 
      
      Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiResponse", $response);

      return $response['data'];
  }

  public static function  CreatePaymentData($withShipping = false){


        $Settings =  SettingsService::getAllSetting($withsecretKey = true);
        $ShopUrls = pluginApp(ShopUrls::class); 

        $paymentMethodName = NetsEasyPayHelper::getMethodByMopId(
                                    pluginApp(Checkout::class)->getPaymentMethodId()
                            );

        $paymentMethodName = ucfirst(strtolower(str_replace(PluginConfiguration::PAYMENT_KEY_EASY, '', $paymentMethodName)));
        
        $tokens = AccessToken::where('status', '=', 1) ;
        $Token = !empty($tokens) ? $tokens[0] :  WebHookHandler::generate_New_token();
        

        $WebhooksEvents = [
          "payment.charge.created.v2",
          "payment.charge.failed",
          "payment.refund.initiated.v2",
          "payment.refund.initiated",
          "payment.refund.completed",
          "payment.refund.failed",
          "payment.cancel.created",
          "payment.cancel.failed"
        ];
        
        $webhooks = [];

        foreach ($WebhooksEvents as $key => $WebhooksEvent) {
          $webhooks[] = [
                          "eventName"=> $WebhooksEvent,
                          "url"=> NetsEasyPayHelper::getDomain().'/rest/nexi/webhooks',
                          "authorization"=>$Token->token_value,
                          "headers"=> [
                                        ["X-token"=> $Token->token_value]
                                      ]
                        ];
        }
        $Paymentdata = [
                          'checkout' => [
                                "integrationType" => "EmbeddedCheckout",
                                "url" => NetsEasyPayHelper::getDomain().$ShopUrls->checkout,
                                "termsUrl"=> NetsEasyPayHelper::getDomain(),
                                "merchantHandlesConsumerData"=> $Settings['merchanthandlesconsumerdata'],
                                "charge"=> false,
                                "merchantHandlesShippingCost" => true,
                                "consumerType"=> [
                                  "default"=> "B2C",
                                  "supportedTypes"=> [
                                      "B2B","B2C"
                                   ]
                                  ],
                                
                          ],
                          "paymentMethodsConfiguration" => [
                            [ "name" => $paymentMethodName ]
                          ],
                          "notifications" => [
                            "webhooks"=> $webhooks
                          ]

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
       
      
       $Paymentdata['checkout']['consumer']['billingAddress']  = $billingAddress['address'];
       
        
       
       

        foreach ($BasketItem as $key => $item) {
            
          $Variation = VariationHelper::findbyIds([$item->variationId]);

          $taxRate =  $item->vat;
          $price = $item->price * 100;
          $netPrice = round($item->price / (1+($taxRate/100)) * 100);
          $grossTotal = $item->quantity * $price;
          // $netTotalAmount = round($grossTotal * 100/(100+$taxRate));
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
            'Payload' => $Paymentdata
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
   
}




