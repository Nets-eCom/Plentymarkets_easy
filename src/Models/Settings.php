<?php

namespace NetsEasyPay\Models;

use Plenty\Modules\Plugin\DataBase\Contracts\Model;
use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use NetsEasyPay\Configuration\PluginConfiguration;
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
        return PluginConfiguration::PLUGIN_NAME.'::Settings';
    }

    public static function all(): array
    {
        return pluginApp(DataBase::class)
                  ->query(Settings::class)
                  ->get();
      
    }
    public static function find($fields=[], $values=[], $operator=['='])
    {

        if( !count($fields) || !count($values) || count($values) != count($fields)){
            return false;
        }
        
        $query = pluginApp(DataBase::class)->query(Settings::class);

        foreach ($fields as $key => $field)
        {
            $query->where($field, array_key_exists($key,$operator)? $operator[$key]:'=', $values[$key]);
        }

        return $query->get();
                                        
    }


   


      
}