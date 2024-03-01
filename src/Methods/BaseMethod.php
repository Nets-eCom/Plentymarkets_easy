<?php

namespace NetsEasyPay\Methods;

use NetsEasyPay\Helper\NetsEasyPayHelper;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Frontend\Services\AccountService;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Account\Contact\Models\ContactAllowedMethodOfPayment;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Modules\Payment\Method\Services\PaymentMethodBaseService;
use Plenty\Plugin\Application;
use NetsEasyPay\Services\SettingsService;
use Plenty\Plugin\Translation\Translator;
use Plenty\Modules\Webshop\Contracts\UrlBuilderRepositoryContract;
use NetsEasyPay\Configuration\PluginConfiguration;

/**
 * Class BaseMethod
 * @package NetsEasyPay\Methods
 */
class BaseMethod extends PaymentMethodBaseService
{
    protected $app;

    /** @var BasketRepositoryContract */
    protected $basketRepo;

    /** @var  SettingsService */
    protected $settings;

    /** @var  Checkout */
    protected $checkout;

    /** @var AccountService */
    protected $accountService;

    /** @var NetsEasyPayHelper */
    protected $NetsEasyPayHelper;

    /** @var Translator */
    protected $translator;


    /**
     * NetsEasyPayPaymentMethod constructor.
     * @param BasketRepositoryContract   $basketRepo
     * @param SettingsService            $service
     * @param Checkout                   $checkout
     * @param AccountService             $accountService
     * @param NetsEasyPayHelper                $NetsEasyPayHelper
     * @param Translator                 $translator
     */
    public function __construct(  BasketRepositoryContract    $basketRepo,
                                  SettingsService             $service,
                                  Checkout                    $checkout,
                                  AccountService              $accountService,
                                  NetsEasyPayHelper           $NetsEasyPayHelper,
                                  Translator                  $translator,
                                  Application                 $app
                                  )
    {
        $this->basketRepo     = $basketRepo;
        $this->settings       = $service;
        $this->checkout       = $checkout;
        $this->accountService = $accountService;
        $this->NetsEasyPayHelper    = $NetsEasyPayHelper;
        $this->translator     = $translator;
        $this->app     = $app;
    }

    /**
     * Check whether NetsEasyPay is active or not
     *
     * @return bool
     * @throws \Plenty\Exceptions\ValidationException
     */
    public function isActive(): bool
    {
        return false;
    }

    /**
     * Get NetsEasyPaySourceUrl
     *
     * @param string $lang
     * @return string
     */
    public function getSourceUrl(string $lang = 'de'): string
    {
        if ($this->settings->getSetting('info_page_toggle')) {

            $lang = $this->getLanguage();
            
            $infoPageType = $this->settings->getSetting('info_page_type');

            switch ($infoPageType)
            {
                case 'internal':
                    $categoryId = (int) $this->settings->getSetting('internal_info_page');
                    if($categoryId  > 0)
                    {
                        /** @var NetsEasyPayHelper $NetsEasyPayHelper */
                        $NetsEasyPayHelper = pluginApp(NetsEasyPayHelper::class);
                        $urlBuilderRepository = pluginApp(UrlBuilderRepositoryContract::class);

                        $urlQuery = $urlBuilderRepository->buildCategoryUrl($categoryId, $lang);

                        $defaultLanguage = $NetsEasyPayHelper->getWebstoreConfig()->defaultLanguage;
                        $includeLanguage = false;
                        if ($lang != $defaultLanguage) {
                            $includeLanguage = true;
                        }

                        return $NetsEasyPayHelper->getDomain() . $urlQuery->toRelativeUrl($includeLanguage);
                    }
                    return '';
                case 'external':
                    return $this->settings->getSetting('external_info_page');
            }
        }

        return '';
    }

    /**
     * Get NetsEasyPay Icon
     *
     * @param string $lang
     * @return string
     */
    public function getIcon(string $lang = 'de'): string
    {
        if(!$this->settings->getSetting('logo_type_external'))
        {
            $lang = $this->getLanguage();

            if ($lang == 'de') {
                $icon = $this->app->getUrlPath(strtolower(PluginConfiguration::PLUGIN_NAME)).'/images/icon_de.jpg';
            } else {
                $icon = $this->app->getUrlPath(strtolower(PluginConfiguration::PLUGIN_NAME)).'/images/icon_en.jpg';
            }

            return $icon;
        }
        else
        {
            
            return $this->settings->getSetting('logo_url');
        }
    }

    /**
     * Get shown name
     *
     * @param string $lang
     * @return string
     */
    public function getName(string $lang = 'de'): string
    {
        return $this->translator->trans(PluginConfiguration::PLUGIN_NAME."::PaymentMethods.".PluginConfiguration::PAYMENT_KEY_EASY);
        
    }

    /**
     * Get the description of the payment method.
     *
     * @param string $lang
     * @return string
     */
    public function getDescription(string $lang = 'de'): string
    {
        return $this->translator->trans(PluginConfiguration::PLUGIN_NAME."::PaymentMethods.".PluginConfiguration::PAYMENT_KEY_EASY."Description");
    }

    /**
     * Get the name for the backend
     *
     * @param string $lang
     * @return string
     */
    public function getBackendName(string $lang = 'de'):string
    {
        return $this->translator->trans(PluginConfiguration::PLUGIN_NAME."::PaymentMethods.".PluginConfiguration::PAYMENT_KEY_EASY,[],$lang);
    }
    
    /**
     * Check if it is allowed to switch to this payment method
     *
     * @param int|null $orderId
     * @return bool
     * @throws \Plenty\Exceptions\ValidationException
     */
    public function isSwitchableTo(int $orderId = null):bool
    {

        return false;
    }

    /**
     * Check if it is allowed to switch from this payment method
     *
     * @return bool
     */
    public function isSwitchableFrom(): bool
    {
        return false;
    }

    /**
     * Get the actual frontend language
     *
     * @return string
     */
    private function getLanguage()
    {
        /** @var FrontendSessionStorageFactoryContract $session */
        $session = pluginApp(FrontendSessionStorageFactoryContract::class);
        return $session->getLocaleSettings()->language;
    }

    /**
     * Check if this payment method should be searchable in the backend
     *
     * @return bool
     */
    public function isBackendSearchable():bool
    {
        return true;
    }

    /**
     * Check if this payment method should be active in the backend
     *
     * @return bool
     */
    public function isBackendActive():bool
    {
        return true;
    }


    /**
     * Check if this payment method can handle subscriptions
     *
     * @return bool
     */
    public function canHandleSubscriptions():bool
    {
        return true;
    }

    /**
     * Get the url for the backend icon
     *
     * @return string
     */
    public function getBackendIcon(): string
    {

        return $this->app->getUrlPath(strtolower(PluginConfiguration::PLUGIN_NAME)).'/images/icons/svg/easy.svg';
    }

    /**
     * @param int $customerId
     * @return bool
     */
    public function isGuest($customerId)
    {
        return !$this->accountService->getIsAccountLoggedIn() || $customerId <= 0;
    }

    /**
     * @param Contact $contact
     * @return bool
     */
    public function isExplicitlyAllowedForThisCustomer(Contact $contact = null,$methodOfPaymentId)
    {
        if (is_null($contact)) {
            return false;
        }

        $allowed = $contact->allowedMethodsOfPayment->first(function ($method,$methodOfPaymentId) {
            if ($method instanceof ContactAllowedMethodOfPayment) {
                if ($method->methodOfPaymentId == $methodOfPaymentId && $method->allowed) {
                    return true;
                }
            }
        });


        return $allowed ? true : false;

    }

    /**
     * @return bool
     * @throws \Plenty\Exceptions\ValidationException
     */
    public function hasActiveShippingCountry()
    {
        if (empty($this->settings->getShippingCountries()) || !in_array($this->checkout->getShippingCountryId(), $this->settings->getShippingCountries())) {
            return false;
        } else {
            return true;
        }

    }

    public function isMethodActive($MethodKey){
        
        $basket = $this->basketRepo->load();
        $allowedMethods = $this->settings->getSetting('allowedNexiMethods');

        if(!is_array($allowedMethods) || !in_array($MethodKey, $allowedMethods))
            return false;
        
        
        if (!$this->isGuest($basket->customerId)) {

            $contactRepository = pluginApp(ContactRepositoryContract::class);
            $contact = $contactRepository->findContactById($basket->customerId);

            if (!$this->hasActiveShippingCountry()) {
                if (!$this->isExplicitlyAllowedForThisCustomer($contact,$basket->methodOfPaymentId)) {
                    return false;
                }
            }

        } else {

            if (!$this->hasActiveShippingCountry()) {
                return false;
            }

            $allowedMethodsForGuest = $this->settings->getSetting('allowNexiForGuest');

            if(!is_array($allowedMethodsForGuest) || (!in_array($MethodKey, $allowedMethodsForGuest) && $this->isGuest($basket->customerId))) {
                return false;
            }

        }

        return true;
    }


}
