<?php

namespace NetsEasyPay\Configuration;

class PluginConfiguration
{

    // Plugin
    const PLUGIN_NAME = 'NetsEasyPay';
    const PLUGIN_KEY = 'NexiNetsPaymentPlugin';

    // Plugin payment methods

    /* Default Method */
    const PAYMENT_KEY_EASY = "EasyPay";
    const EASY_FRONTEND_NAME = "Nexi Pay";
     
    /* VISA Method */
    const PAYMENT_KEY_VISA = self::PAYMENT_KEY_EASY."VISA";
    const VISA_FRONTEND_NAME = "Nexi VISA";

    /* MASTERCARD Method */
    const PAYMENT_KEY_MASTERCARD = self::PAYMENT_KEY_EASY."MASTERCARD";
    const MASTERCARD_FRONTEND_NAME = "Nexi MASTERCARD";

    /* AMERICANEXPRESS Method */ 
    const PAYMENT_KEY_AMERICANEXPRESS = self::PAYMENT_KEY_EASY."AMERICANEXPRESS";
    const AMERICANEXPRESS_FRONTEND_NAME = "Nexi AMERICANEXPRESS";

    /* SWISH Method */
    const PAYMENT_KEY_SWISH = self::PAYMENT_KEY_EASY."SWISH";
    const SWISH_FRONTEND_NAME = "Nexi SWISH";

    /* EASYINVOICE Method */
    const PAYMENT_KEY_EASYINVOICE = self::PAYMENT_KEY_EASY."EASYINVOICE";
    const EASYINVOICE_FRONTEND_NAME = "Nexi EASYINVOICE";

    /* EASYINSTALLMENT Method */
    const PAYMENT_KEY_EASYINSTALLMENT = self::PAYMENT_KEY_EASY."EASYINSTALLMENT";
    const EASYINSTALLMENT_FRONTEND_NAME = "Nexi EASYINSTALLMENT";

    /* ARVATO Method */
    const PAYMENT_KEY_ARVATO = self::PAYMENT_KEY_EASY."ARVATO";
    const ARVATO_FRONTEND_NAME = "Nexi ARVATO";

    /* VIPPS Method */
    const PAYMENT_KEY_VIPPS = self::PAYMENT_KEY_EASY."VIPPS";
    const VIPPS_FRONTEND_NAME = "Nexi VIPPS";

    /* MOBILEPAY Method */
    const PAYMENT_KEY_MOBILEPAY = self::PAYMENT_KEY_EASY."MOBILEPAY";
    const MOBILEPAY_FRONTEND_NAME = "Nexi MOBILEPAY";

    /* PAYPAL Method */
    const PAYMENT_KEY_PAYPAL = self::PAYMENT_KEY_EASY."PAYPAL";
    const PAYPAL_FRONTEND_NAME = "Nexi PAYPAL";

    /* DANKORT Method */
    const PAYMENT_KEY_DANKORT = self::PAYMENT_KEY_EASY."DANKORT";
    const DANKORT_FRONTEND_NAME = "Nexi DANKORT";

    /* RATEPAYINVOICE Method */
    const PAYMENT_KEY_RATEPAYINVOICE = self::PAYMENT_KEY_EASY."RATEPAYINVOICE";
    const RATEPAYINVOICE_FRONTEND_NAME = "Nexi RATEPAYINVOICE";

    /* RATEPAYSEPA Method */
    const PAYMENT_KEY_RATEPAYSEPA = self::PAYMENT_KEY_EASY."RATEPAYSEPA";
    const RATEPAYSEPA_FRONTEND_NAME = "Nexi RATEPAYSEPA";

    /* SOFORT Method */
    const PAYMENT_KEY_SOFORT = self::PAYMENT_KEY_EASY."SOFORT";
    const SOFORT_FRONTEND_NAME = "Nexi SOFORT";

    /* TRUSTLY Method */
    const PAYMENT_KEY_TRUSTLY = self::PAYMENT_KEY_EASY."TRUSTLY";
    const TRUSTLY_FRONTEND_NAME = "Nexi TRUSTLY";


    /* APPLEPAY Method */
    const PAYMENT_KEY_APPLEPAY = self::PAYMENT_KEY_EASY."APPLEPAY";
    const APPLEPAY_FRONTEND_NAME = "Nexi APPLE PAY";

    // order properties


    const PAYMENTID_ORDER_PROPERTY = 'NexiPaymentId';
    

    public static $paymentMethods =  [
                                        
                                        [
                                            'Key' => self::PAYMENT_KEY_VISA,
                                            'Name' => self::VISA_FRONTEND_NAME,
                                            'Class' => \NetsEasyPay\Methods\VisaMethod::class,
                                        ],
                                        [
                                            'Key' => self::PAYMENT_KEY_MASTERCARD,
                                            'Name' => self::MASTERCARD_FRONTEND_NAME,
                                            'Class' => \NetsEasyPay\Methods\MastercardMethod::class
                                        ],
                                        [
                                            'Key' => self::PAYMENT_KEY_AMERICANEXPRESS,
                                            'Name' => self::AMERICANEXPRESS_FRONTEND_NAME,
                                            'Class' => \NetsEasyPay\Methods\AmericanExpressMethod::class
                                        ],
                                        [
                                            'Key' => self::PAYMENT_KEY_SWISH,
                                            'Name' => self::SWISH_FRONTEND_NAME,
                                            'Class' => \NetsEasyPay\Methods\SwishMethod::class
                                        ],
                                        [
                                            'Key' => self::PAYMENT_KEY_EASYINVOICE,
                                            'Name' => self::EASYINVOICE_FRONTEND_NAME, 
                                            'Class' => \NetsEasyPay\Methods\EasyInvoiceMethod::class
                                        ],
                                        [
                                            'Key' => self::PAYMENT_KEY_EASYINSTALLMENT,
                                            'Name' => self::EASYINSTALLMENT_FRONTEND_NAME, 
                                            'Class' => \NetsEasyPay\Methods\EasyInstallmentMethod::class
                                        ],
                                        // [
                                        //     'Key' => self::PAYMENT_KEY_EASYCAMPAIGN,
                                        //     'Name' => self::EASYCAMPAIGN_FRONTEND_NAME,
                                        //     'Class' => \NetsEasyPay\Methods\EasyCampaignMethod::class
                                        // ],
                                        [
                                            'Key' => self::PAYMENT_KEY_ARVATO,
                                            'Name' => self::ARVATO_FRONTEND_NAME,
                                            'Class' => \NetsEasyPay\Methods\ArvatoMethod::class
                                        ],
                                        [
                                            'Key' => self::PAYMENT_KEY_VIPPS,
                                            'Name' => self::VIPPS_FRONTEND_NAME,
                                            'Class' => \NetsEasyPay\Methods\VippsMethod::class
                                        ],
                                        [
                                            'Key' => self::PAYMENT_KEY_MOBILEPAY,
                                            'Name' => self::MOBILEPAY_FRONTEND_NAME,
                                            'Class' => \NetsEasyPay\Methods\MobilePayMethod::class
                                        ],
                                        
                                        [
                                            'Key' => self::PAYMENT_KEY_PAYPAL,
                                            'Name' => self::PAYPAL_FRONTEND_NAME,
                                            'Class' => \NetsEasyPay\Methods\PaypalMethod::class
                                        ],
                                        
                                        [
                                            'Key' => self::PAYMENT_KEY_DANKORT,
                                            'Name' => self::DANKORT_FRONTEND_NAME,
                                            'Class' => \NetsEasyPay\Methods\DankortMethod::class
                                        ],
                                        
                                        [
                                            'Key' => self::PAYMENT_KEY_RATEPAYINVOICE,
                                            'Name' => self::RATEPAYINVOICE_FRONTEND_NAME,
                                            'Class' => \NetsEasyPay\Methods\RatePayInvoiceMethod::class
                                        ],
                                        [
                                            'Key' => self::PAYMENT_KEY_RATEPAYSEPA,
                                            'Name' => self::RATEPAYSEPA_FRONTEND_NAME,
                                            'Class' => \NetsEasyPay\Methods\RatePaySepaMethod::class
                                        ],
                                        [
                                            'Key' => self::PAYMENT_KEY_SOFORT,
                                            'Name' => self::SOFORT_FRONTEND_NAME,
                                            'Class' => \NetsEasyPay\Methods\SofortMethod::class
                                        ],
                                        [
                                            'Key' => self::PAYMENT_KEY_TRUSTLY,
                                            'Name' => self::TRUSTLY_FRONTEND_NAME,
                                            'Class' => \NetsEasyPay\Methods\TrustlyMethod::class
                                        ],
                                        [
                                            'Key' => self::PAYMENT_KEY_APPLEPAY,
                                            'Name' => self::APPLEPAY_FRONTEND_NAME,
                                            'Class' => \NetsEasyPay\Methods\ApplePayMethod::class
                                        ],
                                        
                                        
                                   ];
}
