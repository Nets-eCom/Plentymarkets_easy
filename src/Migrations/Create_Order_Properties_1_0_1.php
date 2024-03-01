<?php

namespace NetsEasyPay\Migrations;


use NetsEasyPay\Helper\NetsEasyPayHelper;
use NetsEasyPay\Configuration\PluginConfiguration;

class Create_Order_Properties_1_0_1
{
    /**
     * Create the settings table
    */
    public function run()
    {
        try
        {
            $properties = [
                PluginConfiguration::PAYMENTID_ORDER_PROPERTY,
            ];
            
            NetsEasyPayHelper::CreateInitialProperty($properties);
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
        }
    }
}