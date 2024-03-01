<?php

namespace NetsEasyPay\Models;

use Plenty\Modules\Plugin\DataBase\Contracts\Model;
use NetsEasyPay\Configuration\PluginConfiguration;
/**
 * Class ShippingCountrySettings
 *
 * @property int $id
 * @property int $plentyId
 * @property int $shippingCountryId
 */
class ShippingCountrySettings extends Model
{
    const MODEL_NAMESPACE = 'NetsEasyPay\Models\ShippingCountrySettings';

    public $id;
    public $plentyId;
    public $shippingCountryId;


    /**
     * @return string
     */
    public function getTableName():string
    {
        return PluginConfiguration::PLUGIN_NAME.'::ShippingCountrySettings';
    }
}