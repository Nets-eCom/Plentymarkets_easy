<?php //strict

namespace NetsEasyPay\Helper;


use Plenty\Modules\Helper\Services\WebstoreHelper;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
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
    public function createMopIfNotExists()
    {
        // Check whether the ID of the plentyNetsEasy payment method has been created

        $paymentMethods = PluginConfiguration::$paymentMethods;

        foreach ($paymentMethods as $key => $method) {
            if(self::getNetsEasyPayMopId($method['Key']) == 'no_paymentmethod_found'){
                
                $paymentMethodData = array( 
                    'pluginKey' => PluginConfiguration::PLUGIN_KEY,
                    'paymentKey' => $method['Key'],
                    'name' => $method['Name']);
    
                 $this->paymentMethodRepository->createPaymentMethod($paymentMethodData);
            }
        }

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

    public static function CancelNetsEasyPayment($OrderId){

        $NetsPayment = self::getNetsEasypaymentFromOrder($OrderId);
        
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
                'currency' => $NetsPayment->currency,
                'total'    => $NetsPayment->amount,
                'paymentId'=> $NetsPayment->hash,
                'status'   => $NetsPayment->status
        ]);

        $NetsEasyPayment = NetsEasyPayService::getNetsEasyPaymentByID($NetsPayment->hash);

        if(!$NetsEasyPayment){
            Logger::error(__FUNCTION__, "NetsEasyPay::Debug.ApiError", ['NetsPayment' => $NetsPayment],'orderId',$OrderId);
            return false;
        }
            
        if(!empty($NetsEasyPayment["payment"]["charges"]) ){
             // call refund method : with full refund
             $response = NetsEasyPayService::RefundPayment($NetsPayment->hash,[],0);

             if($response['refundId']){
                 //Change Status of the payment in plenty to refunded
                 self::ChangePlentyPaymentStatus($NetsPayment->id,Payment::STATUS_REFUNDED);
                            
            }else{
                Logger::error(__FUNCTION__, "NetsEasyPay::Debug.ApiError",$response);
            }

             return true;
        }

        //Cancel the given payment.
         $response = NetsEasyPayService::CancelPayment($NetsPayment->hash);

        //change plentypayment status to Canceled
        if(!$response['error']){
            self::ChangePlentyPaymentStatus($NetsPayment->id,Payment::STATUS_CANCELED);
        }else{
            // add log for error ;
            $payload = [
                'type' => 'warning',
                'contents' => [
                       'subject' => "NE - Cancel Event: OrderId " . $OrderId,
                       'body'=> 'NetsEasyPay::Debug.ApiError'
                ]
            ];
        
            Notification::AddNotification($payload);

            Logger::error(__FUNCTION__, "NetsEasyPay::Debug.ApiError",$response);
        }
                
        


    }
    public static function ChargeNetsEasyPayment($OrderId){
         
        
        $NetsPayment = self::getNetsEasypaymentFromOrder($OrderId);
        
        if(!$NetsPayment){
            
            Logger::error(__FUNCTION__, "NetsEasyPay::ErrorMessages.NoPaymentFound", [
                'NetsPayment' => $NetsPayment,
            ],'orderId',$OrderId);
            
            $payload = [
                'type' => 'warning',
                'contents' => [
                       'subject' => "NE - Charge Event: OrderId " . $ReturnOrderID,
                       'body'=> 'NetsEasyPay::ErrorMessages.NoPaymentFound'
                ]
            ];
        
            Notification::AddNotification($payload);
            
            return false;

        }


        $response = NetsEasyPayService::ChargePayment($NetsPayment->hash);
          
        //Change plenty Payment's status to Approved/refused

        if($response && !$response['error'] ){
               
                
                 $chargeId = $response['chargeId'] ?? 'ChargeId';

                 self::UpdatePlentyPaymentStatus($NetsPayment->id,Payment::STATUS_APPROVED,$chargeId);
                
                 Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ChargePaymentResponse",$response);
      
        }else{
             //$response['error'] declined or non valid operation 

             if( $NetsPayment->status != Payment::STATUS_APPROVED){
                  self::ChangePlentyPaymentStatus($NetsPayment->id,Payment::STATUS_REFUSED);
             }

             Logger::error(__FUNCTION__, "NetsEasyPay::Debug.ApiError",$response);
             
             $payload = [
                'type' => 'warning',
                'contents' => [
                       'subject' => "NE - Charge Event: OrderId " . $OrderId,
                       'body'=> 'NetsEasyPay::Debug.ApiError'
                ]
            ];
        
            Notification::AddNotification($payload);

            
                
        }
        

        

    }
    public static function RefundNetsEasyPayment($ReturnOrderID){

        $paymentContract = pluginApp(PaymentRepositoryContract::class);
        $orderRepository = pluginApp(OrderRepositoryContract::class);

        $ReturnOrder = $orderRepository->findById($ReturnOrderID);
        
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
        $NetsPayment = self::getNetsEasypaymentFromOrder($parentOrderId);
        
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
        
        $paymentId = $NetsPayment->hash;
       

        $orderItemData = [];
        $amount = 0;

        foreach ($ReturnOrder->orderItems as $key => $item) {
            $price =  $item->amounts[0]->priceGross * 100;
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
            
            if($item->typeId == 6){
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


        if($response['refundId']){
             //Create Payment in Plenty for refund / return  Order
              $PaymentInfo = [
                                'currency' => $NetsPayment->currency,
                                'amount' => $amount/100,
                                'id' => $response['refundId'],
                                'mopId' => $NetsPayment->mopId,
                                'refundId' => $response['refundId'],
                                'paymentId' => $NetsPayment->hash,
                                'type' => 'debit'
                            ];

              // create payment only if it's not a return order
             $ReturnPayment = NetsEasyPayHelper::CreatePlentyPayment($PaymentInfo,$ReturnOrderID);
             
             if($ReturnPayment instanceof Payment){
                    //change plentypayment status for return Order  to PPROVED
                    self::ChangePlentyPaymentStatus($ReturnPayment->id,Payment::STATUS_APPROVED);
             }else{
                Logger::error(__FUNCTION__, "NetsEasyPay::ErrorMessages.CanNotCreatePlentyPayment", [
                    'PaymentInfo' => $PaymentInfo,
                 ]);

                 $payload = [
                    'type' => 'warning',
                    'contents' => [
                           'subject' => "NE - Refund Event: OrderId " . $ReturnOrderID,
                           'body'=> 'NetsEasyPay::ErrorMessages.CanNotCreatePlentyPayment'
                    ]
                ];
            
                Notification::AddNotification($payload);
             }
            

        }else{

            $payload = [
                'type' => 'warning',
                'contents' => [
                       'subject' => "NE - Refund Event: OrderId " . $ReturnOrderID,
                       'body'=> 'NetsEasyPay::Debug.ApiError'
                ]
            ];
        
            Notification::AddNotification($payload);

            Logger::error(__FUNCTION__, "NetsEasyPay::Debug.ApiError",$response);
        }
        

    }

    public static function ChangePlentyPaymentStatus($id,$status){
        
        $paymentContract = pluginApp(PaymentRepositoryContract::class);
        $payment = $paymentContract->getPaymentById($id);
    
        $payment->status = $status;

        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.NewPlentyPayment", $payment );

        return $paymentContract->updatePayment($payment);
        

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
        
        $NetsPayments = [];
       
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

        if(!empty($NetsPayments))
             return $NetsPayments[0];

        return null;
    }

    /**
     * Load the ID of the payment method for the given plugin key
     * Return the ID for the payment method
     *
     * @return string|int
     */
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

    public static function getMethodName(string $lang = 'de'): string
    {  
        return pluginApp(Translator::class)->trans(PluginConfiguration::PLUGIN_NAME."::PaymentMethods.".PluginConfiguration::PAYMENT_KEY_EASY);
    }
    /**
     * Create a payment in plentymarkets
     *
     * @param array $paymentInfo
     * @param string $orderId
     * @return Payment
     */

    public static function CreatePlentyPayment($paymentInfo,$orderId)
    {
        $paymentRepository = pluginApp(PaymentRepositoryContract::class);
        $paymentOrderRelationRepository = pluginApp(PaymentOrderRelationRepositoryContract::class);
        $orderRepository = pluginApp(OrderRepositoryContract::class);

        try{
            
            $paymentId = $paymentInfo['paymentId'];
            $refundId = $paymentInfo['refundId'];
            $chargeId = $paymentInfo['chargeId'];

            $payment = pluginApp(Payment::class);
            $payment->mopId           = $paymentInfo['mopId'];
            $payment->transactionType = Payment::TRANSACTION_TYPE_BOOKED_POSTING;
            $payment->status          = $chargeId ? Payment::STATUS_APPROVED : Payment::STATUS_CAPTURED;
            $payment->currency        = $paymentInfo['currency'];
            $payment->amount          = $paymentInfo['amount'];
            $payment->hash            = $paymentInfo['id'];
            $payment->type            = $paymentInfo['type'];
            

            $text = $refundId ?  'refundId: ' .$refundId . ' paymentId: '. $paymentId: 
                    ($chargeId ?  'chargeId: ' .$chargeId . ' paymentId: '. $paymentId:
                                 'paymentId: '. $paymentId) ;
                                


            $paymentProperties[] = self::createPaymentProperty(PaymentProperty::TYPE_TRANSACTION_ID, $orderId);
            $paymentProperties[] = self::createPaymentProperty(PaymentProperty::TYPE_ORIGIN, Payment::ORIGIN_PLUGIN);
            $paymentProperties[] = self::createPaymentProperty(PaymentProperty::TYPE_BOOKING_TEXT,$text );

            
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
        }

    }

    /*
     * Create a PaymentProperty
     *
     * @param int    $typeId
     * @param string $value
     *
     * @return PaymentProperty $paymentProperty
     */
    private static function createPaymentProperty($typeId, $value)
    {
        /** @var PaymentProperty $paymentProperty */
        $paymentProperty = pluginApp( \Plenty\Modules\Payment\Models\PaymentProperty::class );
        $paymentProperty->typeId = $typeId;
        $paymentProperty->value = $value;
        return $paymentProperty;
    }

    /**
     * @return string
     */
    public static function getDomain()
    {
        $webstoreConfig = self::getWebstoreConfig();

        $domain = $webstoreConfig->domainSsl;
        
        if (strpos($domain, 'master.plentymarkets') || $domain == 'http://dbmaster.plenty-showcase.de' || $domain == 'http://dbmaster-beta7.plentymarkets.eu' || $domain == 'http://dbmaster-stable7.plentymarkets.eu') {
            $domain = 'https://master.plentymarkets.com';
        }

        return $domain;
    }

    /**
     * @return WebstoreConfiguration
     */
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
