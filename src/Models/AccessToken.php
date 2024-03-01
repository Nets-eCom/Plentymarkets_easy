<?php
namespace NetsEasyPay\Models;

use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use Plenty\Modules\Plugin\DataBase\Contracts\Model;
use NetsEasyPay\Configuration\PluginConfiguration;



class AccessToken extends Model
{
    const MODEL_NAMESPACE = 'NetsEasyPay\Models\AccessToken';
    /**
     * @var int
     */
    public $id = 0;
    /**
     * @var string
     */
    public $token_value = 0;
    /**
     * @var string
     */
    public $status = 0;
     
   
    public function getTableName(): string
    {
        return PluginConfiguration::PLUGIN_NAME.'::AccessToken';
    }

    public static function find(int $id)
    {
        return pluginApp(DataBase::class)->query(AccessToken::class)
                                         ->where('id', '=', $id)
                                         ->get()[0];
    }


    public static function all(): array
    {
        return pluginApp(DataBase::class)
                  ->query(AccessToken::class)
                  ->get();
      
    }

    public static function where($identifier, $operator, $value): array
    {
        return pluginApp(DataBase::class)->query(AccessToken::class)
                                         ->where($identifier, $operator, $value)
                                         ->get();

    }



    public static function createOrupdate($data){

        $setting = pluginApp(AccessToken::class);

        if(array_key_exists('id', $data)){
            $setting = self::find($data['id']);
            if(!$setting)
               return [];
        }
            
        $setting->token_value  = $data["token_value"] ?? $setting->token_value ;
        $setting->status  = $data["status"] ?? $setting->status;

        pluginApp(DataBase::class)->save($setting);

        return json_encode($setting);
    }

    public static function delete(int $id)
    {
       
        $model    = self::find($id);

        return pluginApp(DataBase::class)->delete($model);
    }





}
