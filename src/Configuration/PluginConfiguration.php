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
    const EASY_FRONTEND_NAME = "Easy Pay";
     
    /* VISA Method */
    const PAYMENT_KEY_VISA = "EasyPayVISA";
    const VISA_FRONTEND_NAME = "Easy VISA";

    /* MASTERCARD Method */
    const PAYMENT_KEY_MASTERCARD = "EasyPayMASTERCARD";
    const MASTERCARD_FRONTEND_NAME = "Easy MASTERCARD";

    /* AMERICANEXPRESS Method */
    const PAYMENT_KEY_AMERICANEXPRESS = "EasyPayAMERICANEXPRESS";
    const AMERICANEXPRESS_FRONTEND_NAME = "Easy AMERICANEXPRESS";

    /* SWISH Method */
    const PAYMENT_KEY_SWISH = "EasyPaySWISH";
    const SWISH_FRONTEND_NAME = "Easy SWISH";

    /* EASYINVOICE Method */
    const PAYMENT_KEY_EASYINVOICE = "EasyPayEASYINVOICE";
    const EASYINVOICE_FRONTEND_NAME = "Easy EASYINVOICE";

    /* EASYINSTALLMENT Method */
    const PAYMENT_KEY_EASYINSTALLMENT = "EasyPayEASYINSTALLMENT";
    const EASYINSTALLMENT_FRONTEND_NAME = "Easy EASYINSTALLMENT";

    /* ARVATO Method */
    const PAYMENT_KEY_ARVATO = "EasyPayARVATO";
    const ARVATO_FRONTEND_NAME = "Easy ARVATO";

    /* VIPPS Method */
    const PAYMENT_KEY_VIPPS = "EasyPayVIPPS";
    const VIPPS_FRONTEND_NAME = "Easy VIPPS";

    /* MOBILEPAY Method */
    const PAYMENT_KEY_MOBILEPAY = "EasyPayMOBILEPAY";
    const MOBILEPAY_FRONTEND_NAME = "Easy MOBILEPAY";

    /* PAYPAL Method */
    const PAYMENT_KEY_PAYPAL = "EasyPayPAYPAL";
    const PAYPAL_FRONTEND_NAME = "Easy PAYPAL";

    /* DANKORT Method */
    const PAYMENT_KEY_DANKORT = "EasyPayDANKORT";
    const DANKORT_FRONTEND_NAME = "Easy DANKORT";

    /* RATEPAYINVOICE Method */
    const PAYMENT_KEY_RATEPAYINVOICE = "EasyPayRATEPAYINVOICE";
    const RATEPAYINVOICE_FRONTEND_NAME = "Easy RATEPAYINVOICE";

    /* RATEPAYSEPA Method */
    const PAYMENT_KEY_RATEPAYSEPA = "EasyPayRATEPAYSEPA";
    const RATEPAYSEPA_FRONTEND_NAME = "Easy RATEPAYSEPA";

    /* SOFORT Method */
    const PAYMENT_KEY_SOFORT = "EasyPaySOFORT";
    const SOFORT_FRONTEND_NAME = "Easy SOFORT";

    /* TRUSTLY Method */
    const PAYMENT_KEY_TRUSTLY = "EasyPayTRUSTLY";
    const TRUSTLY_FRONTEND_NAME = "Easy TRUSTLY";


    public static $paymentMethods =  [
                                        [
                                            'Key' => self::PAYMENT_KEY_EASY,
                                            'Name' => self::EASY_FRONTEND_NAME,
                                            'Class' => \NetsEasyPay\Methods\BaseMethod::class,
                                        ],
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
                                        
                                        
                                   ];
}
