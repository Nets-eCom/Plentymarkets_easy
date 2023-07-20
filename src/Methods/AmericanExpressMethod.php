<?php

namespace NetsEasyPay\Methods;


use NetsEasyPay\Configuration\PluginConfiguration;


class AmericanExpressMethod extends BaseMethod
{

  

    /**
     * Check whether the method is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return false;
    }

    /**
     * @param string $lang
     * @return string
     */
    public function getBackendName(string $lang = 'de'): string
    {
        return $this->translator->trans(PluginConfiguration::PLUGIN_NAME."::PaymentMethods.".PluginConfiguration::PAYMENT_KEY_AMERICANEXPRESS);
    }

     /**
     * Get shown name
     *
     * @param string $lang
     * @return string
     */
    public function getName(string $lang = 'de'): string
    {
        return $this->translator->trans(PluginConfiguration::PLUGIN_NAME."::PaymentMethods.".PluginConfiguration::PAYMENT_KEY_AMERICANEXPRESS);
        
    }

    public function getDescription(string $lang = 'de'): string
    {
        return $this->translator->trans(PluginConfiguration::PLUGIN_NAME."::PaymentMethods.".PluginConfiguration::PAYMENT_KEY_AMERICANEXPRESS."Description");
    }
}
