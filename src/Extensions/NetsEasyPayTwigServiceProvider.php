<?php

namespace NetsEasyPay\Extensions;

use Plenty\Plugin\Templates\Extensions\Twig_Extension;

use NetsEasyPay\Services\SettingsService;
use NetsEasyPay\Helper\SessionHelper;
use NetsEasyPay\Helper\NetsEasyPayHelper;

class NetsEasyPayTwigServiceProvider extends Twig_Extension
{
    /**
     * Return the name of the extension. The name must be unique.
     *
     * @return string The name of the extension
     */
    public function getName():string
    {
        return "NetsEasyPay_Extension_TwigServiceProvider";
    }

    /**
     * Return a list of filters to add.
     *
     * @return array The list of filters to add.
     */
    public function getFilters():array
    {
        return [];
    }

    /**
     * Return a list of functions to add.
     *
     * @return array the list of functions to add.
     */
    public function getFunctions():array
    {
        return [];
    }

    /**
     * Return a map of global helper objects to add.
     *
     * @return array the map of helper objects to add.
     */
    public function getGlobals():array
    {
        return [
            "NetsEasyPay" => [
                "settings"          => pluginApp( SettingsService::class ),
                "sessionStorage"    => pluginApp( SessionHelper::class ),
                "MethodId"          => NetsEasyPayHelper::getNetsEasyPayMopId(),
                "MethodName"        => NetsEasyPayHelper::getMethodName()
            ]
        ];
    }
}