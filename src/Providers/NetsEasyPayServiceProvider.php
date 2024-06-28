<?php //strict

namespace NetsEasyPay\Providers;


use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Payment\Events\Checkout\ExecutePayment;
use Plenty\Modules\Payment\Events\Checkout\GetPaymentMethodContent;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Modules\Wizard\Contracts\WizardContainerContract;
use Plenty\Plugin\ServiceProvider;
use Plenty\Plugin\Templates\Twig;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodContainer;
use Plenty\Modules\EventProcedures\Services\Entries\ProcedureEntry;
use Plenty\Modules\EventProcedures\Services\EventProceduresService;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Modules\Basket\Events\Basket\AfterBasketChanged;
use Plenty\Modules\Basket\Events\Basket\AfterBasketCreate;
use Plenty\Modules\Basket\Events\BasketItem\AfterBasketItemAdd;
use Plenty\Modules\Frontend\Events\FrontendUpdateInvoiceAddress;
use Plenty\Modules\Frontend\Events\FrontendUpdateDeliveryAddress;
use Plenty\Modules\Frontend\Events\FrontendPaymentMethodChanged;
use Plenty\Plugin\Translation\Translator;

use IO\Helper\ResourceContainer;

use NetsEasyPay\Procedures\ChargeEventProcedure;
use NetsEasyPay\Procedures\CancelEventProcedure;
use NetsEasyPay\Procedures\RefundEventProcedure;



use NetsEasyPay\Assistants\NetsEasyPayAssistant;
use NetsEasyPay\Extensions\NetsEasyPayTwigServiceProvider;
use NetsEasyPay\Helper\Plenty\Utils;
use NetsEasyPay\Helper\NetsEasyPayHelper;
use NetsEasyPay\Helper\Logger;
use NetsEasyPay\Helper\SessionHelper;
use NetsEasyPay\Services\NetsEasyPayServiceHttp as NetsEasyPayService;
use NetsEasyPay\Configuration\PluginConfiguration;


/**
 * Class NetsEasyPayServiceProvider
 * @package NetsEasyPay\Providers
 */
class NetsEasyPayServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->getApplication()->register(NetsEasyPayRouteServiceProvider::class);
        $this->getApplication()->bind(RefundEventProcedure::class);
        $this->getApplication()->bind(CancelEventProcedure::class);
        $this->getApplication()->bind(ChargeEventProcedure::class);
    }

    /**
     * Boot additional services for the payment method
     *
     * @param Twig $twig
     * @param NetsEasyPayHelper $paymentHelper
     * @param PaymentMethodContainer $payContainer
     * @param Dispatcher $eventDispatcher
     * @param WizardContainerContract $wizardContainerContract
     */
    public function boot(
        Twig $twig,
        NetsEasyPayHelper $paymentHelper,
        SessionHelper $sessionHelper,
        PaymentMethodContainer $payContainer,
        EventProceduresService $eventProceduresService,
        Dispatcher $eventDispatcher,
        WizardContainerContract $wizardContainerContract
    ) {

        $eventDispatcher->listen('IO.Resources.Import', function (ResourceContainer $container) {
            // This automatically adds the Scripts.twig file to be loaded "after load".
            // So there is no need to connect a container
            $container->addScriptTemplate('NetsEasyPay::Scripts');
            $container->addStyleTemplate(' NetsEasyPay::Css');
        }, 0);


        // Register NEXI Assistant
        $wizardContainerContract->register('payment-netseasypay-assistant', NetsEasyPayAssistant::class);

        $twig->addExtension(NetsEasyPayTwigServiceProvider::class);

        // Register NEXI payment method in the payment method container
        $paymentMethods = PluginConfiguration::$paymentMethods;
        foreach ($paymentMethods as $key => $method) {

                $payContainer->register(
                    PluginConfiguration::PLUGIN_KEY . '::' . $method['Key'],
                    $method['Class'],
                    $this->paymentMethodEvents()
                );
            
        }

        // Listen for AfterBasketChanged
        $eventDispatcher->listen(
            AfterBasketChanged::class,
            function (AfterBasketChanged $event) use ($paymentHelper) {
                NetsEasyPayHelper::UpdateNetsEasyPayment();
            }
        );
        $eventDispatcher->listen(
            FrontendUpdateInvoiceAddress::class,
            function ($event) {

                $sessionHelper = pluginApp(SessionHelper::class);

                $PaymentId =  $sessionHelper->getValue('EasyPaymentId') ? NetsEasyPayService::CreatePaymentId() : null;

                $sessionHelper->setValue('EasyPaymentId', $PaymentId);
            },
            0
        );
        $eventDispatcher->listen(
            FrontendUpdateDeliveryAddress::class,
            function ($event) {

                $sessionHelper = pluginApp(SessionHelper::class);

                $PaymentId =  $sessionHelper->getValue('EasyPaymentId') ? NetsEasyPayService::CreatePaymentId() : null;

                $sessionHelper->setValue('EasyPaymentId', $PaymentId);
            },
            0
        );
        $eventDispatcher->listen(
            FrontendPaymentMethodChanged::class,
            function ($event) {
                
            },
            0
        );

        



        // Listen for the event that gets the payment method content
        $eventDispatcher->listen(
            GetPaymentMethodContent::class,
            function (GetPaymentMethodContent $event) {
               
                 $sessionHelper = pluginApp(SessionHelper::class);
                 $translator = pluginApp(Translator::class);
                 // get the name of payment-method 
                 $NexiSelectedMethod = NetsEasyPayHelper::getMethodByMopId($event->getMop());

                if ($NexiSelectedMethod) {
                     
                    $SessionPaymentId = $sessionHelper->getValue('EasyPaymentId');
                   
                    if ($SessionPaymentId && 
                        $NexiSelectedMethod == $sessionHelper->getValue('NexiSelectedMethod') ) { // a paymentid already exist
                        
                        $event->setValue($SessionPaymentId);
                        $event->setType(GetPaymentMethodContent::RETURN_TYPE_CONTINUE);


                        return true;
  
                    }

                    $EasyPaymentId = NetsEasyPayService::CreatePaymentId();
                        
                    if($EasyPaymentId){

                            $sessionHelper->setValue('EasyPaymentId', $EasyPaymentId);
                            $sessionHelper->setValue('NexiSelectedMethod', $NexiSelectedMethod);
                            $event->setValue($EasyPaymentId);
                            $event->setType(GetPaymentMethodContent::RETURN_TYPE_CONTINUE);
                          
  
                            return true;
                    }

                    
                    $event->setValue($translator->trans('NetsEasyPay::ErrorMessages.CanNotCreatePaymentId'));
                    $event->setType(GetPaymentMethodContent::RETURN_TYPE_ERROR);
                }
            }
        );

        // Listen for the event that executes the payment
        $eventDispatcher->listen(
            ExecutePayment::class,
            function (ExecutePayment $event) use ($sessionHelper) {
                
                $SelectedMethod = NetsEasyPayHelper::getMethodByMopId($event->getMop());

                if ($SelectedMethod) {
                    $basketRepo = pluginApp(BasketRepositoryContract::class);
                    $orderRepository = pluginApp(OrderRepositoryContract::class);


                    $EasyPaymentId = $sessionHelper->getValue('EasyPaymentId');

                    $order = $orderRepository->findById($event->getOrderId());
                    $basket = $basketRepo->load();
                    $MopId = $event->getMop();
                    $OrderPropertyType = Utils::GetOrderPropertyType(PluginConfiguration::PAYMENTID_ORDER_PROPERTY);
                    //update order property with payment id
                    NetsEasyPayHelper::UpdateNexiPaymentIdprops($OrderPropertyType->id,$order->id, $EasyPaymentId);
                    
                    //Update NetsEasy by adding the orderID as referrence
                    NetsEasyPayService::UpdateNetsEasyPaymentRef($order->id, $EasyPaymentId);


                    // delete EasyPaymentId from the session
                    $sessionHelper->setValue('EasyPaymentId', null);
                    $sessionHelper->setValue('NexiSelectedMethod', null);
              
                    Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ExecutePaymentPayload", [
              
                                          'EasyPaymentId' => $EasyPaymentId,
                                          'order' => $order,
                                          'basket' => $basket,
                                          'MopId' => $MopId,
              
              
                    ]);
              
                     $event->setType(GetPaymentMethodContent::RETURN_TYPE_CONTINUE);

                     return $event->setValue('The payment has been executed successfully!');
                                 

                }


                $sessionHelper->setValue('EasyPaymentId', null);
                $sessionHelper->setValue('NexiSelectedMethod', null);
            }
        );


        // Charge payment Event Procedure
        $eventProceduresService->registerProcedure(
            'NetsEasyPaymentMethod',
            ProcedureEntry::PROCEDURE_GROUP_ORDER,
            [
                'de' => 'Nexi Checkout Belastung der Zahlung',
                'en' => 'Nexi Checkout Charge payment'
            ],
            ChargeEventProcedure::class . '@run'
        );
        // Register Refund Event Procedure
        $eventProceduresService->registerProcedure(
            'NetsEasyPaymentMethod',
            ProcedureEntry::PROCEDURE_GROUP_ORDER,
            [
                'de' => 'Nexi Checkout Rückerstattung der Zahlung',
                'en' => 'Nexi Checkout Refund payment'
            ],
            RefundEventProcedure::class . '@run'
        );
        // Register Cancel Event Procedure
        $eventProceduresService->registerProcedure(
            'NetsEasyPaymentMethod',
            ProcedureEntry::PROCEDURE_GROUP_ORDER,
            [
                'de' => 'Nexi Checkout Stornierung der Zahlung',
                'en' => 'Nexi Checkout Cancel payment'
            ],
            CancelEventProcedure::class . '@run'
        );
    }

    /**
     * Return an array of events
     *
     * @return array
     */
    private function paymentMethodEvents(): array
    {
        return  [
            AfterBasketChanged::class,
            AfterBasketItemAdd::class,
            AfterBasketCreate::class
        ];
    }
}
