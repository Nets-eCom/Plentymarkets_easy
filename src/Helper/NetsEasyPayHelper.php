<?php //strict

namespace NetsEasyPay\Helper;


use Plenty\Modules\Helper\Services\WebstoreHelper;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use NetsEasyPay\Helper\Plenty\Order\OrderHelper;
use Plenty\Modules\Payment\Contracts\PaymentOrderRelationRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Modules\Payment\Models\PaymentProperty;
use Plenty\Modules\System\Models\WebstoreConfiguration;
use Plenty\Plugin\Log\Loggable;
use NetsEasyPay\Services\NetsEasyPayServiceHttp as NetsEasyPayService ;
use NetsEasyPay\Configuration\PluginConfiguration;
use Plenty\Plugin\Translation\Translator;
use NetsEasyPay\Helper\Plenty\Notification;
use NetsEasyPay\Helper\Plenty\Utils;
use NetsEasyPay\Services\SettingsService;
use Plenty\Plugin\Http\Response;
/**
 * Class NetsEasyPayHelper
 *
 * @package NetsEasyPay\Helper
 */
class NetsEasyPayHelper
{
    use Loggable;

    private $contactBank;

    /**
     * @var WebstoreConfiguration
     */
    private $webstoreConfig;

    /**
     * @var PaymentMethodRepositoryContract $paymentMethodRepository
     */
    private $paymentMethodRepository;

    


    /**
     * NetsEasyPayHelper constructor.
     *
     * @param PaymentMethodRepositoryContract $paymentMethodRepository
     */
    public function __construct(PaymentMethodRepositoryContract $paymentMethodRepository)
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    /**
     * Create the ID of the payment method if it doesn't exist yet
     */
    public static function createMopIfNotExists()
    {
        // Check whether the ID of the plentyNetsEasy payment method has been created

        $paymentMethods = PluginConfiguration::$paymentMethods;
        $paymentMethodRepository = pluginApp(PaymentMethodRepositoryContract::class);
        $new_created_methods = [];

        foreach ($paymentMethods as $key => $method) {

                $NexiMethod =  self::getNetsEasyPayMopId($method['Key']);

                if( $NexiMethod == 'no_paymentmethod_found'){
                         
                    $paymentMethodRepository->createPaymentMethod([
                            'pluginKey' => PluginConfiguration::PLUGIN_KEY,
                            'paymentKey' => $method['Key'],
                            'name' => $method['Name']
                         ]);
                    
                    $new_created_methods[] = $method['Key'];

                }


        }

        return $new_created_methods;

    }
    public static function getAppleVerificationText(){

        $response = pluginApp(Response::class);
        $Settings =  SettingsService::getAllSetting();
       
       //return $Settings['AppleVerification'] ? $Settings['AppleVerificationText'] : null;
        return $Settings['AppleVerification'] ? $response->make(
            $Settings['AppleVerificationText'],
            $response::HTTP_OK,
            ['Content-Type' => 'text/plain']
        ) : null;

    }
    
    /**
     * Update Netseasy payment on basket change
     */
    public static function UpdateNetsEasyPayment(){

        $sessionHelper = pluginApp(SessionHelper::class);
        $PaymentId =  $sessionHelper->getValue('EasyPaymentId');
        
        if(!$PaymentId){
            Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.UpdatePaymentViaApi", 'no nets easy payment exist for this session');
            return false;
        }
            
        return NetsEasyPayService::UpdatedPaymentId($PaymentId);

    }
    public static function UpdateNetsEasyPaymentRef($OrderId,$PaymentId){

        $response = NetsEasyPayService::UpdateNetsEasyPaymentRef($PaymentId,$OrderId);
        
        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.UpdatePaymentReferenceViaApi", $PaymentId);
       
        return $response;
    }
    public static function CreateInitialProperty($names){
        
        return Utils::CreateInitialProperty($names);
        
    }
    public static function UpdateNexiPaymentIdprops($PropertyId,$OrderId,$PaymentId){

        return Utils::setOrderProperty($PropertyId,$OrderId,$PaymentId);

    }
    public static function CancelNetsEasyPayment($OrderId){

        $Settings =  SettingsService::getAllSetting();
        $NetsPayment = self::getNetsEasypaymentIdFromOrder($OrderId);
        
        if(!$NetsPayment){
            
            Logger::error(__FUNCTION__, "NetsEasyPay::ErrorMessages.NoPaymentFound", [
                'NetsPayment' => $NetsPayment,
            ],'orderId',$OrderId);
            
            $payload = [
                'type' => 'warning',
                'contents' => [
                       'subject' => "NE - Cancel Event: OrderId " . $OrderId,
                       'body'=> 'NetsEasyPay::ErrorMessages.NoPaymentFound'
                ]
            ];
        
            Notification::AddNotification($payload);
            
            return false;
    
        }

        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.CancelPaymentPayload", [
                'NetsPayment' => $NetsPayment,
        ]);

        $NetsEasyPayment = NetsEasyPayService::getNetsEasyPaymentByID($NetsPayment);
        
        if(!$NetsEasyPayment){
           
            Logger::error(__FUNCTION__, "NetsEasyPay::Debug.ApiError", ['NetsPayment' => $NetsPayment],'orderId',$OrderId);
           
            $allowedOrderStatusChangeOnAPIfailure = $Settings['allowedOrderStatusChangeOnAPIfailure'];
            //change order status.
            if($allowedOrderStatusChangeOnAPIfailure)
                OrderHelper::setOrderStatusId($OrderId,$Settings['APIcallFaildStatus']);
            
            return false;
        }
        //check if payment has charges

        if(!empty($NetsEasyPayment["payment"]["charges"]) ){
            // call refund method : with full refund
            NetsEasyPayService::RefundPayment($NetsPayment,[],0); 
            
            return true;
       }

        //Cancel the given payment.
        $response = NetsEasyPayService::CancelPayment($NetsPayment);

        if ($response['error']) {
          
            Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiError",  $response);
            
            $allowedOrderStatusChangeOnAPIfailure = $Settings['allowedOrderStatusChangeOnAPIfailure'];
            //change order status.
            if($allowedOrderStatusChangeOnAPIfailure)
                OrderHelper::setOrderStatusId($OrderId,$Settings['APIcallFaildStatus']);
        } 
        
    }
    public static function ChargeNetsEasyPayment($OrderId){
         
        $Settings =  SettingsService::getAllSetting();
        //get paymentId from order :

        $NetsPayment = self::getNetsEasypaymentIdFromOrder($OrderId);
        
        if(!$NetsPayment){
            
            Logger::error(__FUNCTION__, "NetsEasyPay::ErrorMessages.NoPaymentFound", [
                'NetsPayment' => $NetsPayment,
            ],'orderId',$OrderId);
            
            $payload = [
                'type' => 'warning',
                'contents' => [
                       'subject' => "NE - Charge Event: OrderId " . $OrderId,
                       'body'=> 'NetsEasyPay::ErrorMessages.NoPaymentFound'
                ]
            ];
        
            Notification::AddNotification($payload);
            
            return false;

        }

        $response = NetsEasyPayService::ChargePayment($NetsPayment);  

        if ($response['error']) {
          
            Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiError",  $response);
            
            $allowedOrderStatusChangeOnAPIfailure = $Settings['allowedOrderStatusChangeOnAPIfailure'];
            //change order status.
            if($allowedOrderStatusChangeOnAPIfailure)
                OrderHelper::setOrderStatusId($OrderId,$Settings['APIcallFaildStatus']);
        } 

    }
    public static function RefundNetsEasyPayment($ReturnOrderID){
        
        $Settings =  SettingsService::getAllSetting();
 
        $ReturnOrder = pluginApp(OrderRepositoryContract::class)->findById($ReturnOrderID);
        
        $parentOrderId = null;
        
        // get parent of this order;
        foreach ($ReturnOrder->orderReferences as $key => $reference) {
            if($reference->referenceType == "parent"){
                 $parentOrderId = $reference->originOrderId;
                 break;
             }
        }
       
        if(!$parentOrderId){
            Logger::error(__FUNCTION__, "NetsEasyPay::ErrorMessages.NoParentOrderFound", [
                'OrderId' => $ReturnOrderID,
            ],'orderId',$ReturnOrderID); 
            
            $payload = [
                'type' => 'warning',
                'contents' => [
                       'subject' => "NE - Refund Event: OrderId " . $ReturnOrderID,
                       'body'=> 'NetsEasyPay::ErrorMessages.NoParentOrderFound'
                ]
            ];
        
            Notification::AddNotification($payload);

            return false;
        }
            
        //get Netseasy PaymentId from parent Order
        $NetsPayment = self::getNetsEasypaymentIdFromOrder($parentOrderId);

        if(!$NetsPayment){
            
            Logger::error(__FUNCTION__, "NetsEasyPay::ErrorMessages.NoPaymentFound", [
                'NetsPayment' => $NetsPayment,
            ],'orderId',$ReturnOrderID);
            
            $payload = [
                'type' => 'warning',
                'contents' => [
                       'subject' => "NE - Refund Event: OrderId " . $ReturnOrderID,
                       'body'=> 'NetsEasyPay::ErrorMessages.NoPaymentFound'
                ]
            ];
        
            Notification::AddNotification($payload);
            
            return false;
    
        }
        
        $paymentId = $NetsPayment;
       

        $orderItemData = [];
        $amount = 0;

        foreach ($ReturnOrder->orderItems as $key => $item) {
            
            $grossTotal =  round($item->quantity * $item->amounts[0]->priceGross,2) * 100;
            $netTotalAmount = round($item->quantity * $item->amounts[0]->priceNet,2)* 100;
            $taxamount = round($grossTotal - $netTotalAmount,2);

            

            if($item->typeId != 6 && $item->typeId != 3){
                
                $orderItemData[] = [
                                        "reference" => $item->itemVariationId,
                                        "name" => $item->orderItemName,
                                        "quantity" => $item->quantity,
                                        "unit"=> "pcs",
                                        "unitPrice" => round($item->amounts[0]->priceNet,2)*100,
                                        "taxRate"=> $item->vatRate*100,
                                        "taxAmount"=> $taxamount,
                                        "grossTotalAmount" => $grossTotal,
                                        "netTotalAmount" => $netTotalAmount
                                    ];

                $amount += $grossTotal;
            }
            
            if($item->typeId == 6 && $item->amounts[0]->priceOriginalGross != 0 ){
                $orderItemData[] = [
                                        "reference"=>"Shipping",
                                        "name"=> "Shipping",
                                        "quantity" => $item->quantity,
                                        "unit" => "NA",
                                        "unitPrice" => round($item->amounts[0]->priceNet,2)*100,
                                        "taxRate"=> $item->vatRate*100,
                                        "taxAmount"=> $taxamount,
                                        "grossTotalAmount" => $grossTotal,
                                        "netTotalAmount" => $netTotalAmount
                                    ];

                $amount += $grossTotal;
            }

        }
        
        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.RefundPaymentPayload", [
            'returnOrder' =>$ReturnOrder,
            'parentOrderId' => $parentOrderId,
            'refundorderItemData' => $orderItemData,
            'refundAmount' => $amount,
            'paymentId' => $paymentId
        ]);

    
        $response = NetsEasyPayService::RefundPayment($paymentId,$orderItemData,$amount);

        // create payment in the credit note and set with refundId as a hash
        $refundId = $response['data']['refundId'];
        $chargeId = $response['chargeId'];

        if($refundId){
   
                foreach ($ReturnOrder->properties as $key => $property) {
                    if($property->typeId == 3){
                      $MopId = $property->value;
                    }
                }

                $PaymentInfo = [
                    'currency' => $ReturnOrder->amounts[0]->currency,
                    'amount' => $amount/100,
                    'id' => $refundId,
                    'status' => Payment::STATUS_AWAITING_APPROVAL,
                    'mopId' => $MopId,
                    'reference' => $paymentId,
                    'chargeId' => $chargeId,
                    'refundId' => $refundId,
                    'type' => 'debit',
                    'unaccountable' => true
                ];


                return self::CreatePlentyPayment($PaymentInfo, $ReturnOrder->id);

        }else{
            
            $payload = [
                'type' => 'warning',
                'contents' => [
                       'subject' => "NE - Refund Event: OrderId " . $ReturnOrderID,
                       'body'=> 'NetsEasyPay::Debug.ApiError'
                ]
            ];
        
            Notification::AddNotification($payload);

            Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ApiError",  $response);
            
            $allowedOrderStatusChangeOnAPIfailure = $Settings['allowedOrderStatusChangeOnAPIfailure'];
            //change order status.
            if($allowedOrderStatusChangeOnAPIfailure)
                OrderHelper::setOrderStatusId($ReturnOrderID,$Settings['APIcallFaildStatus']);

            Logger::error(__FUNCTION__, "NetsEasyPay::Debug.ApiError",$response);

            return null;
        }

    
    }
    public static function CreateCreditNoteforOrder($OrderId,$paymentId=null,$ChargeId = null,$status = null){


       $payload = [];
       $quantities = [];
      
       
       if($paymentId && $ChargeId){

            
            // get Payment from the api
            $NetsEasyPayment = NetsEasyPayService::getNetsEasyPaymentByID($paymentId);

            if(!$NetsEasyPayment){
                Logger::error(__FUNCTION__, "NetsEasyPay::Debug.ApiError", ['NetsPayment' => $paymentId]);
                return null;
            }

            // get items for this chargeId
            $Charges = $NetsEasyPayment["payment"]["charges"];
            $ChargeItems = null;

            foreach ( $Charges as $key => $Charge) {
                if($Charge['chargeId'] == $ChargeId){
                    $ChargeItems = $Charge['orderItems'];
                    break;
                }
            }
            if(!$ChargeItems){ 
                Logger::error(__FUNCTION__, "NetsEasyPay::ErrorMessages.NoChargeFound", [
                    'NetsPaymentId' => $paymentId,
                    'NetsEasyPayment' => $NetsEasyPayment,
                    'ChargeId' => $ChargeId   
                ]);

                return null;
            }

            $ParentOrder =  OrderHelper::find($OrderId);

            $copyShippingCosts = false;
            $shippingCostsValue = 0;
            $setShippingCostsToZero =  true;

            // get orderItemIds from the parent order
            foreach ($ChargeItems as $i => $ChargeItem) {
                
                foreach ($ParentOrder->orderItems as $k => $orderItem) {
                    if($ChargeItem['reference'] == $orderItem->itemVariationId ){
                        $quantities[] = [
                            "orderItemId" => $orderItem->id,
                            "quantity"=> $ChargeItem['quantity'],
                            "orderItemName"=> $ChargeItem['name']
                        ];
                        break;
                    }
                } 

                if($ChargeItem['reference'] == 'Shipping' ){
                    $copyShippingCosts = true;
                    $shippingCostsValue = $ChargeItem['grossTotalAmount'] / 100;
                    $setShippingCostsToZero =  false;
                }
                
            }

            
            $payload = [
                "copyShippingCosts" => $copyShippingCosts,
                "shippingCostsValue" =>$shippingCostsValue,
                "setShippingCostsToZero" => $setShippingCostsToZero,
                "includeCouponsIntoOrderItemPrice" => false,
                "quantities"=> $quantities
              ];
            
              
       }

       Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.CreditNotePayload", [
        'NetsPaymentId' => $paymentId,
        'NetsEasyPayment' => $NetsEasyPayment,
        'ChargeId' => $ChargeId,
        'payload'  => $payload,
        'status' => $status
      ]);
      
       $crediteNote =  OrderHelper::createCreditNoteforOrder($OrderId,$payload,$status);

       return $crediteNote;


    }
    public static function CreateChargePayment($data,$status,$unaccountable=0)
    {
  
      $EasyPaymentId = $data['paymentId'];
      
      $chargeId = $data['chargeId'];
      $amount = $data['amount']['amount'];
  
      // get reference
      $NetsEasyPayment = NetsEasyPayService::getNetsEasyPaymentByID($EasyPaymentId);
  
      if(!$NetsEasyPayment){
        Logger::error(__FUNCTION__, "NetsEasyPay::Debug.ApiError", ['NetsPayment' => $EasyPaymentId]);
        return null;
      }
            
  
      $orderId = $NetsEasyPayment["payment"]["orderDetails"]["reference"];
  
      $Order = pluginApp(OrderRepositoryContract::class)->findById($orderId);
       
  
      foreach ($Order->properties as $key => $property) {
        if($property->typeId == 3){
          $MopId = $property->value;
        }
      }
      // create payment object for plenty 
      $PaymentInfo = [
                      'currency' => $Order->amounts[0]->currency,
                      'amount' => $amount/100,
                      'id' => $chargeId,
                      'mopId' => $MopId,
                      'reference' => $EasyPaymentId,
                      'chargeId' => $chargeId,
                      'type' => 'credit',
                      'status' => $status,
                      'unaccountable' => $unaccountable
      ];
  
          //Create plenty Payment and asigne it to order-> to do check if payment created
       $payment = self::CreatePlentyPayment($PaymentInfo, $Order->id);
       
       return [
        'payment' => $payment,
        'orderId' => $orderId
       ];
    
       
    }

    public static function ChangePlentyPaymentStatus($id,$status){
        
        $paymentContract = pluginApp(PaymentRepositoryContract::class);
        $payment = $paymentContract->getPaymentById($id);
    
        $payment->status = $status;

        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.NewPlentyPayment", $payment );

        return $paymentContract->updatePayment($payment);
        

    }
    public static function AddPlentyPaymentReference($id,$reference){
        

       
        $payment = pluginApp(PaymentRepositoryContract::class)->getPaymentById($id);
        
        $properties = $payment->properties;
        
        foreach ($properties as $key => $property) {
            if($property->typeId == PaymentProperty::TYPE_BOOKING_TEXT){
                $property->value .= $reference;
            }
        }

        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.NewPlentyPayment", $payment );

        return pluginApp(PaymentRepositoryContract::class)->updatePayment($payment);
        

    }
    public static function UpdatePlentyPaymentStatus($paymentid,$status,$chargeId){
    
        $paymentContract = pluginApp(PaymentRepositoryContract::class);
        $payment = $paymentContract->getPaymentById($paymentid);
    
        $payment->status = $status;
        
        foreach ($payment->properties as $key => $property) {
            if($property->typeId == 3){
                $payment->properties[$key]->value = $property->value . ' chargeId: '.$chargeId;
            } 
        }


        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.NewPlentyPayment", $payment );

        return $paymentContract->updatePayment($payment);
    
    }
    public static function getNetsEasypaymentFromOrder($orderId){

        $paymentContract = pluginApp(PaymentRepositoryContract::class);

        //get all payments of the order
        $payments = $paymentContract->getPaymentsByOrderId($orderId);
        
        $NetsPayments = null;
       
        //search for netseasy payment id linked to the order
        foreach ($payments as $key => $payment) {
            if($payment->method->pluginKey == PluginConfiguration::PLUGIN_KEY){
                $NetsPayments[] = $payment;
            }
        }

            
        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.PaymentsList", [
                'NetsPayments' => $NetsPayments,
                'payments' => $payments
             ]);

      
         return $NetsPayments;


    }
    public static function getNetsEasypaymentIdFromOrder($orderId){


        $Order = pluginApp(OrderRepositoryContract::class)->findById($orderId);
        $OrderPropertyType = Utils::GetOrderPropertyType(PluginConfiguration::PAYMENTID_ORDER_PROPERTY);
        $NetsEasypaymentId = null;

        if(!$OrderPropertyType)
            return null;
        
        foreach ($Order->properties as $key => $property) {
            if($property->typeId == $OrderPropertyType->id){
              $NetsEasypaymentId = $property->value;
            }
        }

         return $NetsEasypaymentId;


    }
    public static function getpaymentByHash($orderId,$paymentHash){

        $paymentContract = pluginApp(PaymentRepositoryContract::class);

        //get all payments of the order
        $payments = $paymentContract->getPaymentsByOrderId($orderId);
       
        $NetsPayment = null;
       
        //search for netseasy payment id linked to the order
        foreach ($payments as $key => $payment) {
            if($payment->hash == $paymentHash){
                $NetsPayment = $payment;
                break;
            }
        }

            
        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.PaymentsList", [
                'NetsPayments' => $NetsPayment,
                'payments' => $payments
             ]);

      
         return $NetsPayment;


    }

    public static function get_payment_from_credit_note($orderId,$refundId){

        $CreditNotes = OrderHelper::getallcreditnotofOrder($orderId);
    
        foreach ($CreditNotes as $key => $CreditNote) {
          $NetsPlentyPayment = self::getpaymentByHash($CreditNote['id'],$refundId);
          if($NetsPlentyPayment) break; 
        }
    
        return $NetsPlentyPayment;
    }
    public static function getNetsEasyPayMopId($PAYMENT_KEY = PluginConfiguration::PAYMENT_KEY_EASY)
    {

        $paymentMethodRepository = pluginApp(PaymentMethodRepositoryContract::class);
        
        $paymentMethods = $paymentMethodRepository->allForPlugin(PluginConfiguration::PLUGIN_KEY);
        
        if( !is_null($paymentMethods) )
        {
            foreach($paymentMethods as $paymentMethod)
            {
                if($paymentMethod->paymentKey == $PAYMENT_KEY )
                {
                    return $paymentMethod->id;
                }
            }
        }

        return 'no_paymentmethod_found';
    }
    public static function getAllNetsEasyPayMopIds()
    {
        $MopIds = [];
        $paymentMethodRepository = pluginApp(PaymentMethodRepositoryContract::class);
        
        $paymentMethods = $paymentMethodRepository->allForPlugin(PluginConfiguration::PLUGIN_KEY);
        
        if( !is_null($paymentMethods) )
        {
            foreach($paymentMethods as $paymentMethod)
            {
                $MopIds[] = $paymentMethod->id;
            }
        }

        return $MopIds;
    }
    public static function getMethodByMopId($mopId)
    {

        $paymentMethodRepository = pluginApp(PaymentMethodRepositoryContract::class);
        
        $paymentMethods = $paymentMethodRepository->allForPlugin(PluginConfiguration::PLUGIN_KEY);
        
        if( !is_null($paymentMethods) )
        {
            foreach($paymentMethods as $paymentMethod)
            {
                if($paymentMethod->id == $mopId )
                {
                    return $paymentMethod->paymentKey;
                }
            }
        }

        return null;
    }

    public static function getMethodName(string $lang = 'de'): string
    {  
        return pluginApp(Translator::class)->trans(PluginConfiguration::PLUGIN_NAME."::PaymentMethods.".PluginConfiguration::PAYMENT_KEY_EASY);
    }
    public static function CreatePlentyPayment($paymentInfo,$orderId)
    {
        $paymentRepository = pluginApp(PaymentRepositoryContract::class);
        $paymentOrderRelationRepository = pluginApp(PaymentOrderRelationRepositoryContract::class);
        $orderRepository = pluginApp(OrderRepositoryContract::class);

        try{
            

            $payment = pluginApp(Payment::class);
            $payment->mopId           = $paymentInfo['mopId'];
            $payment->transactionType = Payment::TRANSACTION_TYPE_BOOKED_POSTING;
            $payment->status          = $paymentInfo['status'];
            $payment->currency        = $paymentInfo['currency'];
            $payment->amount          = $paymentInfo['amount'];
            $payment->hash            = $paymentInfo['id'];
            $payment->type            = $paymentInfo['type'];
            //$payment->unaccountable   = $paymentInfo['unaccountable'];
            
            $reference  = 'PaymentId : ' .$paymentInfo['reference'];
            $reference .= $paymentInfo['chargeId'] ? ' chargeId : ' .$paymentInfo['chargeId'] : '';
            $reference .= $paymentInfo['refundId'] ? ' refundId : ' .$paymentInfo['refundId'] : '';
            
            $paymentProperties[] = self::createPaymentProperty(PaymentProperty::TYPE_TRANSACTION_ID, $orderId);
            $paymentProperties[] = self::createPaymentProperty(PaymentProperty::TYPE_ORIGIN, Payment::ORIGIN_PLUGIN);
            $paymentProperties[] = self::createPaymentProperty(PaymentProperty::TYPE_BOOKING_TEXT,$reference );

            
            $payment->properties = $paymentProperties;
            
 
            Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.PaymentPayload", $payment);
             
             $plenty_payment = $paymentRepository->createPayment($payment);
             
             Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.NewPlentyPayment", [
                 'plenty_payment' => $plenty_payment,
                 'orderId' => $orderId
                
            ]);
             

             $plentyOrder = $orderRepository->findById($orderId);

             Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.PlentyOrder", [
                'plentyOrder' => $plentyOrder,
             ]);
             
             if ($plenty_payment instanceof Payment) {
                $paymentOrderRelationRepository->createOrderRelation($plenty_payment, $plentyOrder);
             }

            

            return $plenty_payment;


        } catch (ReferenceTypeException $ex){
            Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.CanNotCreatePlentyPayment", $ex);
            return null;
        }

    }

    private static function createPaymentProperty($typeId, $value)
    {
        /** @var PaymentProperty $paymentProperty */
        $paymentProperty = pluginApp( \Plenty\Modules\Payment\Models\PaymentProperty::class );
        $paymentProperty->typeId = $typeId;
        $paymentProperty->value = $value;
        return $paymentProperty;
    }
    public static function getDomain()
    {
        $webstoreConfig = self::getWebstoreConfig();

        $domain = $webstoreConfig->domainSsl;
        
        if (strpos($domain, 'master.plentymarkets') || $domain == 'http://dbmaster.plenty-showcase.de' || $domain == 'http://dbmaster-beta7.plentymarkets.eu' || $domain == 'http://dbmaster-stable7.plentymarkets.eu') {
            $domain = 'https://master.plentymarkets.com';
        }

        return $domain;
    }
    public static function getWebstoreConfig()
    {

        $webstoreHelper = pluginApp(WebstoreHelper::class);

        return $webstoreHelper->getCurrentWebstoreConfiguration();
    }

    private function getLanguage()
    {
        return \Locale::getDefault();
    }
}


