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
use Plenty\Modules\Basket\Events\BasketItem\AfterBasketItemRemove;
use Plenty\Modules\Basket\Events\BasketItem\AfterBasketItemUpdate;
use Plenty\Modules\Basket\Events\BasketItem\BeforeBasketItemAdd;
use Plenty\Modules\Frontend\Events\FrontendUpdateInvoiceAddress;
use Plenty\Modules\Frontend\Events\FrontendUpdateDeliveryAddress;

use Plenty\Plugin\Translation\Translator;

use IO\Helper\ResourceContainer;

use NetsEasyPay\Procedures\ChargeEventProcedure;
use NetsEasyPay\Procedures\CancelEventProcedure;
use NetsEasyPay\Procedures\RefundEventProcedure;



use NetsEasyPay\Assistants\NetsEasyPayAssistant;
use NetsEasyPay\Extensions\NetsEasyPayTwigServiceProvider;
use NetsEasyPay\Helper\Plenty\Order\OrderPropertyHelper;
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


        // Register the netseasy Assistant
        $wizardContainerContract->register('payment-netseasypay-assistant', NetsEasyPayAssistant::class);

        $twig->addExtension(NetsEasyPayTwigServiceProvider::class);

        // Register the netseasy payment method in the payment method container
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


        // Listen for Basket Item Add
        $eventDispatcher->listen(
            AfterBasketItemAdd::class,
            function (AfterBasketItemAdd $event) use ($paymentHelper) {
            }
        );

        // Listen for Basket Item remove
        $eventDispatcher->listen(
            AfterBasketItemRemove::class,
            function (AfterBasketItemRemove $event) use ($paymentHelper) {
            }
        );

        // Listen for Basket Item UPDATE
        $eventDispatcher->listen(
            AfterBasketItemUpdate::class,
            function (AfterBasketItemUpdate $event) use ($paymentHelper) {
            }
        );

        // Listen for Before Basket Item Add
        $eventDispatcher->listen(
            BeforeBasketItemAdd::class,
            function (BeforeBasketItemAdd $event) {
            }
        );


        // Listen for the event that gets the payment method content
        $eventDispatcher->listen(
            GetPaymentMethodContent::class,
            function (GetPaymentMethodContent $event) {
                if ($event->getMop() == NetsEasyPayHelper::getNetsEasyPayMopId()) {

                    // check if paymentId Doesn't Exit
                    $sessionHelper = pluginApp(SessionHelper::class);
                    $PaymentId =  $sessionHelper->getValue('EasyPaymentId') ?? NetsEasyPayService::CreatePaymentId();

                    //check if paymentId expired -> to do

                    Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.CreatedPaymentId", [
                        'PaymentId' => $PaymentId,
                    ]);

                    if ($PaymentId) {

                        //set paymentId value into a session variable
                        $sessionHelper->setValue('EasyPaymentId', $PaymentId);
                        $event->setValue($PaymentId);
                        $event->setType(GetPaymentMethodContent::RETURN_TYPE_CONTINUE);

                        return true;
                    }

                    $translator = pluginApp(Translator::class);
                    $event->setValue($translator->trans('NetsEasyPay::ErrorMessages.CanNotCreatePaymentId'));
                    $event->setType(GetPaymentMethodContent::RETURN_TYPE_ERROR);
                }
            }
        );

        // Listen for the event that executes the payment
        $eventDispatcher->listen(
            ExecutePayment::class,
            function (ExecutePayment $event) use ($paymentHelper, $basketRepo, $sessionHelper) {

                if ($event->getMop() == NetsEasyPayHelper::getNetsEasyPayMopId()) {
                    $basketRepo = pluginApp(BasketRepositoryContract::class);
                    $orderRepository = pluginApp(OrderRepositoryContract::class);


                    $EasyPaymentId = $sessionHelper->getValue('EasyPaymentId');

                    $order = $orderRepository->findById($event->getOrderId());
                    $basket = $basketRepo->load();

                    // get NetsEasy Payment Details  -> to do : handle fallback
                    $NetsEasyPayment = NetsEasyPayService::getNetsEasyPaymentByID($EasyPaymentId);



                    //check the payment status from netseasy 
                    $PaymentMethod = $NetsEasyPayment['payment']['paymentDetails']['paymentMethod'] ?? null;
                    // check booked amount --> to do

                    $MopId = $PaymentMethod ?  NetsEasyPayHelper::getNetsEasyPayMopId(PluginConfiguration::PAYMENT_KEY_EASY . strtoupper($PaymentMethod)) : null;
                    $MopId = ($MopId  && $MopId != 'no_paymentmethod_found') ? $MopId : NetsEasyPayHelper::getNetsEasyPayMopId();

                    // Update the order's payment method 
                    OrderPropertyHelper::updateOrCreateValue($order->id, 3, (string) $MopId);


                    // create payment object for plenty 
                    $PaymentInfo = [
                        'currency' => $basket->currency,
                        'amount' => $basket->basketAmount,
                        'id' => $EasyPaymentId,
                        'mopId' => $MopId,
                        'refundId' => null,
                        'paymentId' => $EasyPaymentId,
                        'type' => 'credit',
                        'chargeId' => $NetsEasyPayment['payment']['charges'][0]['chargeId'] ?? null
                    ];

                    //Create plenty Payment and asigne it to order-> to do check if payment created
                    $PlentyPayment = NetsEasyPayHelper::CreatePlentyPayment($PaymentInfo, $order->id);

                    if ($PlentyPayment instanceof Payment) {

                        //Update NetsEasy by adding the orderID as referrence
                        $response = NetsEasyPayService::UpdateNetsEasyPaymentRef($order->id, $EasyPaymentId);

                        // Charge NetsEasy Payment 
                        // NetsEasyPayService::ChargePayment($EasyPaymentId);

                        // delete EasyPaymentId from the session
                        $sessionHelper->setValue('EasyPaymentId', null);

                        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.ExecutePaymentPayload", [

                            'EasyPaymentId' => $EasyPaymentId,
                            'order' => $order,
                            'basket' => $basket,
                            'NetsEasyPayment' => $NetsEasyPayment,
                            'PlentyPayment' => $PlentyPayment,
                            'PaymentMethod' => $PaymentMethod,
                            'MopId' => $MopId,


                        ]);

                        $event->setType(GetPaymentMethodContent::RETURN_TYPE_CONTINUE);
                        return $event->setValue('The payment has been executed successfully!');
                    } else {
                        Logger::debug(__FUNCTION__, "NetsEasyPay::Debug.CanNotCreatePlentyPayment", [
                            'EasyPaymentId' => $EasyPaymentId,
                            'NetsEasyPayment' => $NetsEasyPayment,
                            'PlentyPayment' => $PlentyPayment
                        ]);

                        $event->setType(GetPaymentMethodContent::RETURN_TYPE_ERROR);
                        $translator = pluginApp(Translator::class);

                        return $event->setValue($translator->trans('NetsEasyPay::ErrorMessages.PaymentCannotBeExecuted'));
                    }
                }


                $sessionHelper->setValue('EasyPaymentId', null);
            }
        );


        // Charge payment Event Procedure
        $eventProceduresService->registerProcedure(
            'NetsEasyPaymentMethod',
            ProcedureEntry::PROCEDURE_GROUP_ORDER,
            [
                'de' => 'NetsEasy Belastung der Zahlung',
                'en' => 'NetsEasy Charge payment'
            ],
            ChargeEventProcedure::class . '@run'
        );
        // Register Refund Event Procedure
        $eventProceduresService->registerProcedure(
            'NetsEasyPaymentMethod',
            ProcedureEntry::PROCEDURE_GROUP_ORDER,
            [
                'de' => 'NetsEasy Rückerstattung der Zahlung',
                'en' => 'NetsEasy Refund payment'
            ],
            RefundEventProcedure::class . '@run'
        );
        // Register Cancel Event Procedure
        $eventProceduresService->registerProcedure(
            'NetsEasyPaymentMethod',
            ProcedureEntry::PROCEDURE_GROUP_ORDER,
            [
                'de' => 'NetsEasy Stornierung der Zahlung',
                'en' => 'NetsEasy Cancel payment'
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
