<?php

namespace NetsEasyPay\Models;

use Plenty\Modules\Plugin\DataBase\Contracts\Model;

/**
 * Class Settings
 *
 * @property int $id
 * @property int $webstore
 * @property string $name
 * @property array $value
 * @property string $createdAt
 * @property string $updatedAt
 */
class Settings extends Model
{
    const MODEL_NAMESPACE = 'NetsEasyPay\Models\Settings';

    public $id = 0;
    public $webstore = 0;
    public $name = '';
    public $value = array();
    public $createdAt = '';
    public $updatedAt = '';


    /**
     * @return string
     */
    public function getTableName():string
    {
        return 'NetsEasyPay::settings';
    }
}