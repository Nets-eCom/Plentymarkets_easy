<?php

namespace NetsEasyPay\Assistants;

use NetsEasyPay\Assistants\SettingsHandlers\NetsEasyPayAssistantSettingsHandler;
use NetsEasyPay\Assistants\Steps\SettingsStep;
use Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract;
use Plenty\Modules\System\Contracts\WebstoreRepositoryContract;
use Plenty\Modules\System\Models\Webstore;
use Plenty\Modules\Wizard\Services\WizardProvider;
use Plenty\Plugin\Application;
use Plenty\Plugin\Translation\Translator;

/**
 * Class NetsEasyPayAssistant
 * @package  NetsEasyPay\Assistants
 */
class NetsEasyPayAssistant extends WizardProvider
{
    /**
     * @var CountryRepositoryContract
     */
    private $countryRepository;

    /**
     * @var WebstoreRepositoryContract
     */
    private $webstoreRepository;

    /**
     * @var array
     */
    private $deliveryCountries;

    /**
     * @var string
     */
    private $language;

    /**
     * @var Translator
     */
    protected $translator;

    public function __construct(
        CountryRepositoryContract $countryRepository,
        WebstoreRepositoryContract $webstoreRepository,
        Translator $translator
    ) {
        $this->countryRepository = $countryRepository;
        $this->webstoreRepository = $webstoreRepository;
        $this->translator = $translator;
    }

    /**
     * The Assistant structure
     *
     * @return array
     */
    protected function structure()
    {
        $config = [
            "title" => 'NetsEasyPayAssistant.assistantTitle',
            "shortDescription" => 'NetsEasyPayAssistant.assistantShortDescription',
            "iconPath" => $this->getIcon(),
            "settingsHandlerClass" => NetsEasyPayAssistantSettingsHandler::class,
            "translationNamespace" => "NetsEasyPay",
            "key" => "payment-netseasypay-assistant",
            "topics" => ["payment"],
            'priority' => 990,
            "options" => [
                "config_name" => [
                    "type" => 'select',
                    "defaultValue" => 0,
                    "options" => [
                        "name" => 'NetsEasyPayAssistant.storeName',
                        'required' => true,
                        'listBoxValues' => $this->getWebstoreListForm(),
                    ],
                ],
            ],
            "steps" => [
                "stepZero" => SettingsStep::stepZero(),
                'stepOne' => SettingsStep::stepOne($this->getCountriesListForm()),
                'stepTwo' => SettingsStep::stepTwo(),
                "stepThree" => SettingsStep::stepThree($this->getFrontendIcons()),
                'stepFour' => SettingsStep::stepFour(),
            ]
        ];
        return $config;
    }

    /**
     * @return array
     */
    private function getCountriesListForm()
    {
        if ($this->deliveryCountries === null) {
            /** @var CountryRepositoryContract $countryRepository */
            $countryRepository = pluginApp(CountryRepositoryContract::class);
            $countries = $countryRepository->getCountriesList(true, ['names']);
            $this->deliveryCountries = [];
            $systemLanguage = $this->getLanguage();
            foreach($countries as $country) {
                $name = $country->names->where('lang', $systemLanguage)->first()->name;
                $this->deliveryCountries[] = [
                    'caption' => $name ?? $country->name,
                    'value' => $country->id
                ];
            }
            // Sort values alphabetically
            usort($this->deliveryCountries, function($a, $b) {
                return ($a['caption'] <=> $b['caption']);
            });
        }
        return $this->deliveryCountries;
    }

    /**
     * @return array
     */
    private function getWebstoreListForm()
    {
        $webstores = $this->webstoreRepository->loadAll();
        /** @var Webstore $webstore */
        foreach ($webstores as $webstore) {
            $values[] = [
                "caption" => $webstore->name,
                "value" => $webstore->id,
            ];
        }

        usort($values, function ($a, $b) {
            return ($a['value'] <=> $b['value']);
        });

        return $values;
    }

    /**
     * @return string
     */
    private function getLanguage()
    {
        if ($this->language === null) {
            $this->language =  \Locale::getDefault();
        }

        return $this->language;
    }

    private function getIcon()
    {
        $app = pluginApp(Application::class);

        if ($this->getLanguage() != 'de') {
            return $app->getUrlPath('NetsEasyPay').'/images/icon_en.jpg';
        }

        return $app->getUrlPath('NetsEasyPay').'/images/icon_de.jpg';
    }

    private function getFrontendIcons()
    {
        $app = pluginApp(Application::class);
        $iconsNames = [
                        [
                            'name' => 'visa',
                            'width' =>  50
                        ],
                        [
                            'name' => 'mastercard',
                            'width' =>  35
                        ],
                        [
                            'name' => 'maestro',
                            'width' =>  35
                        ],
                        [
                            'name' => 'vipps',
                            'width' =>  50
                        ],
                        [
                            'name' => 'afterpay',
                            'width' =>  70
                        ],
                        [
                            'name' => 'swish',
                            'width' =>  60
                        ],
                        [
                            'name' => 'mobilepay',
                            'width' =>  60
                        ],
                        [
                            'name' => 'dankort',
                            'width' =>  40
                        ],
                        [
                            'name' => 'paypal',
                            'width' =>  70
                        ],
                        [
                            'name' => 'ratepay',
                            'width' =>  35
                        ],
 

                    ];
        $icons =  [];

        foreach ($iconsNames as $key => $icon) {
            $icons[] = [
                'caption' => $icon['name'],
                'value' => $app->getUrlPath('netseasypay').'/images/icons/svg/'.$icon['name'].'.svg,'.$icon['width']
            ];
        }
       

        return $icons;
   
    }
}
