<?php

namespace NetsEasyPay\Methods;


use NetsEasyPay\Configuration\PluginConfiguration;


class SwishMethod extends BaseMethod
{

  

    const METHOD_KEY = PluginConfiguration::PAYMENT_KEY_SWISH;
    const METHOD_NAME = PluginConfiguration::PLUGIN_NAME."::PaymentMethods.".self::METHOD_KEY;

    /**
     * Check whether the method is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isMethodActive(self::METHOD_KEY);
    }

    /**
     * @param string $lang
     * @return string
     */
    public function getBackendName(string $lang = 'de'): string
    {
        return $this->translator->trans(self::METHOD_NAME);
    }

     /**
     * Get shown name
     *
     * @param string $lang
     * @return string
     */
    public function getName(string $lang = 'de'): string
    {
        return $this->translator->trans(self::METHOD_NAME);
        
    }
    public function getDescription(string $lang = 'de'): string
    {
        return $this->translator->trans(self::METHOD_NAME."Description");
    }

    public function getIcon(string $lang = 'de'): string
    {
        $icon = strtolower(str_replace(PluginConfiguration::PAYMENT_KEY_EASY, '', self::METHOD_KEY));
    
        return $this->app->getUrlPath(strtolower(PluginConfiguration::PLUGIN_NAME)).'/images/icons/svg/'.$icon.'.svg';
         
    }
}
