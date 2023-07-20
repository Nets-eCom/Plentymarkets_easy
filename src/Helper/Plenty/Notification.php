<?php

namespace NetsEasyPay\Helper\Plenty;

use Plenty\Modules\Notifications\Contracts\NotificationsRepositoryContract;
use Plenty\Modules\Helper\Services\WebstoreHelper;
use Plenty\Plugin\Translation\Translator;
use NetsEasyPay\Services\SettingsService;
use NetsEasyPay\Helper\Logger;

class Notification
{

    public static function AddNotification($payload)
    {

        $PluginSettings = pluginApp( SettingsService::class );
        $translator = pluginApp(Translator::class);   
        
        if(!$PluginSettings->getSetting('BackendNotificationEnabled'))
             return false;
        
        $data = [];
        $data['type'] = $payload['type'];
        $data['plentyId'] = self::getWebstoreConfig()->storeIdentifier ;
        $data['source'] = 'NetsEasyPayment';
        $data['channels'] = [
            'NetsEasyPayment-Channel'
        ] ;
        $data['contents'] = [
            'de' => [
                'subject' => $payload['contents']['subject'] ,
                'body'=> $translator->trans($payload['contents']['body'],[],'de'),
            ],
            'en' => [
                'subject' => $payload['contents']['subject'] ,
                'body'=> $translator->trans($payload['contents']['body'],[],'en'),
            ]   
        ];
        
        
        
        //Logger::error(__FUNCTION__, "AddNotification",$data);

        return pluginApp(NotificationsRepositoryContract::class)->addNotification($data);
   
    }

    public static function getWebstoreConfig()
    {

        $webstoreHelper = pluginApp(WebstoreHelper::class);

        return $webstoreHelper->getCurrentWebstoreConfiguration();
    }


}
