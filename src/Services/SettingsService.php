<?php

namespace NetsEasyPay\Services;

use NetsEasyPay\Models\ShippingCountrySettings;
use Plenty\Exceptions\ValidationException;
use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use Plenty\Modules\Plugin\DataBase\Contracts\Query;

use NetsEasyPay\Models\Settings;
use Plenty\Plugin\Application;
use Plenty\Modules\Frontend\Services\SystemService;

class SettingsService extends DatabaseBaseService
{
    /** @var Application */
    private $app;

    /** @var $systemService SystemService */
    protected $systemService;

    /** @var array */
    private $settings = null;

    public function __construct(SystemService $systemService, DataBase $db)
    {
        $this->systemService = $systemService;
        parent::__construct($db);
    }

    public function saveSettings($mode, $settings)
    {
        if ($settings) {
            foreach ($settings as $store => $values) {
                $id = 0;
                $store = (int)str_replace('PID_', '', $store);

                if ($store > 0) {
                    $existValue = $this->getValues(Settings::class, ['name', 'webstore'], [$mode, $store], ['=', '=']);
                    if (isset($existValue) && is_array($existValue)) {
                        if ($existValue[0] instanceof Settings) {
                            $id = $existValue[0]->id;
                        }
                    }

                    /** @var Settings $settingModel */
                    $settingModel = pluginApp(Settings::class);
                    if ($id > 0) {
                        $settingModel->id = $id;
                    }
                    $settingModel->webstore = $store;
                    $settingModel->name = $mode;
                    $settingModel->value = $values;
                    $settingModel->updatedAt = date('Y-m-d H:i:s');

                    if ($settingModel instanceof Settings) {
                        $this->setValue($settingModel);
                    }
                }
            }
            return 1;
        }
    }

    public function saveShippingCountrySettings($settings)
    {
        //delete existing ShippingCountrySettings
        $this->deleteShippingCountrySettingsByPlentyId($settings['plentyId']);

        if ($settings) {
            foreach ($settings['countries'] as $countryId) {
                /** @var ShippingCountrySettings $shippingCountrySettings */
                $shippingCountrySettings = pluginApp(ShippingCountrySettings::class);
                $shippingCountrySettings->plentyId = $settings['plentyId'];
                $shippingCountrySettings->shippingCountryId = $countryId;

                if ($shippingCountrySettings instanceof ShippingCountrySettings) {
                    $this->setValue($shippingCountrySettings);
                }
            }
            return 1;
        }
    }

    public function loadSetting($webstore, $mode)
    {
        $setting = $this->getValues(Settings::class, ['name', 'webstore'], [$mode, $webstore], ['=', '=']);
        if (is_array($setting) && $setting[0] instanceof Settings) {
            return $setting[0]->value;
        }
        return [];
    }

    public function loadSettings($settingType)
    {
        $settings = array();
        $results = $this->getValues(Settings::class);
        if (is_array($results)) {
            foreach ($results as $item) {
                if ($item instanceof Settings && $item->name == $settingType) {
                    $settings[] = [$item->webstore => $item->value];
                }
            }
        }
        return $settings;
    }

    public function getSetting($settingType)
    {
        $plentyId = $this->systemService->getPlentyId();

        if (!isset($this->settings)) {
            $this->settings = $this->loadSetting($plentyId, 'NetsEasyPay');
        }

        if(is_array($this->settings)) {
            foreach ($this->settings as $name => $value) {

                if ($name == $settingType) {
                    
                    if ('checkoutKey' == $settingType &&  $this->settings['UseTestCredentials']) {
                        return $this->settings['checkoutKeyTest'];
                    }

                    return $value;
                }
            }
        }

        return "";
    }

    public static function getAllSetting($withsecretKey = false)
    {
        
        $plentyId = pluginApp(SystemService::class)->getPlentyId();

        $setting = self::getValues(Settings::class, ['name', 'webstore'], ['NetsEasyPay', $plentyId], ['=', '=']);
       
        if (is_array($setting) && $setting[0] instanceof Settings) {
            

            if($setting[0]->value['UseTestCredentials']){
               
                $setting[0]->value['checkoutKey'] = $setting[0]->value['checkoutKeyTest'];
                $setting[0]->value['secretKey']   = $setting[0]->value['secretKeyTest'];
            
            }

            if(!$withsecretKey){
                unset($setting[0]->value['secretKey']);
                unset($setting[0]->value['secretKeyTest']);
            }
            
            return $setting[0]->value;
        }
        
        return [];

    
    }

    public function deleteShippingCountrySettingsByPlentyId($plentyId)
    {
        if ($plentyId > 0) {
            /** @var Query $query */
            $query = $this->dataBase->query(ShippingCountrySettings::MODEL_NAMESPACE);
            $query->where('plentyId', '=', $plentyId);

            /** @var ShippingCountrySettings[] $shippingCountrySettings */
            $shippingCountrySettings = $query->get();

            foreach ($shippingCountrySettings as $shippingSetting) {
                $this->dataBase->delete($shippingSetting);
            }
        }
    }

    /**
     * Load the current activated shipping countries
     *
     * @return mixed|Settings
     * @throws ValidationException
     */
    public function getShippingCountries()
    {
        $plentyId = $this->systemService->getPlentyId();

        return $this->getShippingCountriesByPlentyId($plentyId);
    }

    /**
     * Load the activated shipping countries for plentyId
     *
     * @return mixed
     */
    public function getShippingCountriesByPlentyId($plentyId)
    {
        /** @var Query $query */
        $query = $this->dataBase->query(ShippingCountrySettings::MODEL_NAMESPACE);
        $query->where('plentyId', '=', $plentyId);

        /** @var ShippingCountrySettings[] $shippingCountrySettings */
        $shippingCountrySettings = $query->get();

        $shippingCountriesArray = [];
        foreach ($shippingCountrySettings as $shippingSetting) {
            $shippingCountriesArray[] = (int)$shippingSetting->shippingCountryId;
        }

        return $shippingCountriesArray;
    }


}
