<?php

namespace NetsEasyPay\Migrations;

use NetsEasyPay\Helper\NetsEasyPayHelper;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;
use NetsEasyPay\Models\ShippingCountrySettings;

/** This migration initializes all Settings in the Database */
class CreateShippingCountrySettings_1_0_3
{
    public function run(Migrate $migrate)
    {
        $migrate->createTable(ShippingCountrySettings::class);

        // Create the ID of the payment method if it doesn't exist yet
        NetsEasyPayHelper::createMopIfNotExists();
    }
}
