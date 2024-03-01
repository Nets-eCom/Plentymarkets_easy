<?php

namespace NetsEasyPay\Controllers;

use NetsEasyPay\Services\SettingsService;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Response;
use Plenty\Modules\Frontend\Services\SystemService;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;
use NetsEasyPay\Models\Settings;
use NetsEasyPay\Models\ShippingCountrySettings;
use NetsEasyPay\Methods\MastercardMethod;
class SettingsController extends Controller
{

    public function getSettings(Response $response)
    {
        return $response->json(SettingsService::getAllSetting());
    }
    public function findSettings(){
     

        $webstore = pluginApp(SystemService::class)->getPlentyId();
        $name = 'NetsEasyPay';
  
        $Settings = Settings::find(['name', 'webstore'], [$name, $webstore], ['=', '=']);
  
        return $Settings;
        
        
    }

    public function runMigration(){

      
        pluginApp(Migrate::class)->deleteTable(Settings::class);
        pluginApp(Migrate::class)->deleteTable(ShippingCountrySettings::class);

        pluginApp(Migrate::class)->createTable(Settings::class);
        pluginApp(Migrate::class)->createTable(ShippingCountrySettings::class);

        return 'Migration Successfully Done';
        
    }
    


}