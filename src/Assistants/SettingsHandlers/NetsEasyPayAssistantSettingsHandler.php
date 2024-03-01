<?php

namespace NetsEasyPay\Assistants\SettingsHandlers;
use NetsEasyPay\Helper\NetsEasyPayHelper;
use NetsEasyPay\Services\SettingsService;
use Plenty\Modules\Order\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\System\Contracts\WebstoreRepositoryContract;
use Plenty\Modules\System\Models\Webstore;
use Plenty\Modules\Wizard\Contracts\WizardSettingsHandler;
use Plenty\Modules\Plugin\Contracts\PluginLayoutContainerRepositoryContract;
use Plenty\Modules\Plugin\Models\Plugin;


class NetsEasyPayAssistantSettingsHandler implements WizardSettingsHandler
{
    /**
     * @var Webstore
     */
    private $webstore;

    /**
     * @var Plugin
     */
    private $NetsEasyPayPlugin;
    /**
     * @var Plugin
     */
    private $ceresPlugin;

    /**
     * @param array $parameter
     * @return bool
     */
    public function handle(array $parameter)
    {
        $data = $parameter['data'];
        $webstoreId = $data['config_name'];
        if ((!is_numeric($webstoreId) || $webstoreId < 0) && !$this->isValidUUIDv4($parameter['optionId'])) {
            $webstoreId = $parameter['optionId'];
        }

        $this->saveNetsEasyPaySettings($webstoreId, $data);
        $this->saveNetsEasyPayShippingCountrySettings($webstoreId, $data);
        $this->createContainer($webstoreId, $data);
        $this->activateLegacyPaymentMethod();

        return true;
    }

    /**
     * @param int $webstoreId
     * @param array $data
     */
    private function saveNetsEasyPaySettings($webstoreId, $data)
    {
        $webstore = $this->getWebstore($webstoreId);

        $settings = [
            'PID_' . $webstore->storeIdentifier => [
                'checkoutKey' => $data['checkoutKey'],
                'secretKey' => $data['secretKey'],
                'checkoutKeyTest' => $data['checkoutKeyTest'],
                'secretKeyTest' => $data['secretKeyTest'],
                'UseTestCredentials' => $data['UseTestCredentials'],
                'logo_type_external' => $data['logo_type_external'],
                'logo_url'  => $data['logo_url'],
                'icons' => $data['icons'],
                'BackendNotificationEnabled' => $data['BackendNotificationEnabled'],
                //'immediatecharge' => $data['immediatecharge'],
                'merchanthandlesconsumerdata' => $data['merchanthandlesconsumerdata'],
                'info_page_toggle' => $data['info_page_toggle'],
                'external_info_page'  => $data['external_info_page'],
                'internal_info_page' => $data['internal_info_page'],
                'info_page_type' => $data['info_page_type'],
                'allowNexiForGuest' => $data['allowNexiForGuest'],
                'allowedNexiMethods' => $data['allowedNexiMethods'],
                //'allowedWebHooks' => $data['allowedWebHooks'],
                'allowedOrderStatusChangeChargeEvent' => $data['allowedOrderStatusChangeChargeEvent'],
                'ChargeCompletedStatus' => $data['ChargeCompletedStatus'],
                'ChargeFaildStatus' => $data['ChargeFaildStatus'],
                
                'allowedOrderStatusChangeCancelEvent' => $data['allowedOrderStatusChangeCancelEvent'],
                'CancelCompletedStatus' => $data['CancelCompletedStatus'],
                'CancelFaildStatus' => $data['CancelFaildStatus'],
                
                'allowedOrderStatusChangeRefundEvent' => $data['allowedOrderStatusChangeRefundEvent'],
                'RefundCompletedStatus' => $data['RefundCompletedStatus'],
                'RefundFaildStatus' => $data['RefundFaildStatus'],

                'allowCreditNoteCreationOnRefund' => $data['allowCreditNoteCreationOnRefund'],
                'creditNoteCreationStatus' => $data['creditNoteCreationStatus'],
                'allowedOrderStatusChangeOnAPIfailure' => $data['allowedOrderStatusChangeOnAPIfailure'],
                'APIcallFaildStatus' =>   $data['APIcallFaildStatus'],
                'AppleVerification' =>   $data['AppleVerification'],
                'AppleVerificationText' =>   $data['AppleVerificationText'],
                   
                
            ]
        ];
        /** @var SettingsService $settingsService */
        $settingsService = pluginApp(SettingsService::class);
        $settingsService->saveSettings('NetsEasyPay', $settings);
    }

    /**
     * @param array $data
     */
    private function saveNetsEasyPayShippingCountrySettings($webstoreId, $data)
    {
        $webstore = $this->getWebstore($webstoreId);

        $settings = [
            'plentyId' => $webstore->storeIdentifier,
            'countries' => $data['countries'],
        ];
        /** @var SettingsService $settingsService */
        $settingsService = pluginApp(SettingsService::class);
        $settingsService->saveShippingCountrySettings($settings);
    }

    /**
     * @param int $webstoreId
     * @return Webstore
     */
    private function getWebstore($webstoreId)
    {
        if ($this->webstore === null) {
            /** @var WebstoreRepositoryContract $webstoreRepository */
            $webstoreRepository = pluginApp(WebstoreRepositoryContract::class);
            $this->webstore = $webstoreRepository->findById($webstoreId);
        }

        return $this->webstore;
    }

    /**
     * @param int $webstoreId
     * @param array $data
     */
    private function createContainer($webstoreId, $data)
    {
        $webstore = $this->getWebstore($webstoreId);
        $NetsEasyPayPlugin = $this->getNetsEasyPayPlugin($webstoreId);
        $ceresPlugin = $this->getCeresPlugin($webstoreId);

        if( ($webstore && $webstore->pluginSetId) &&  $NetsEasyPayPlugin !== null && $ceresPlugin !== null) {
            /** @var PluginLayoutContainerRepositoryContract $pluginLayoutContainerRepo */
            $pluginLayoutContainerRepo = pluginApp(PluginLayoutContainerRepositoryContract::class);

            $containerListEntries = [];

            // Default entries
            $containerListEntries[] = $this->createContainerDataListEntry(
                $webstoreId,
                'Ceres::Script.AfterScriptsLoaded',
                'NetsEasyPay\Providers\DataProvider\NetsEasyPayReinitializePaymentScript'
            );

            $containerListEntries[] = $this->createContainerDataListEntry(
                $webstoreId,
                'Ceres::MyAccount.OrderHistoryPaymentInformation',
                'NetsEasyPay\Providers\DataProvider\NetsEasyPayReinitializePayment'
            );

            $containerListEntries[] = $this->createContainerDataListEntry(
                $webstoreId,
                'Ceres::OrderConfirmation.AdditionalPaymentInformation',
                'NetsEasyPay\Providers\DataProvider\NetsEasyPayReinitializePayment'
            );

            if (isset($data['NetsEasyPayPaymentMethodIcon']) && $data['NetsEasyPayPaymentMethodIcon']) {
                $containerListEntries[] = $this->createContainerDataListEntry(
                    $webstoreId,
                    'Ceres::Homepage.PaymentMethods',
                    'NetsEasyPay\Providers\Icon\IconProvider'
                );
            } else {
                $pluginLayoutContainerRepo->removeOne(
                    $webstore->pluginSetId,
                    'Ceres::Homepage.PaymentMethods',
                    'NetsEasyPay\Providers\Icon\IconProvider',
                    $ceresPlugin->id,
                    $NetsEasyPayPlugin->id
                );
            }

            $pluginLayoutContainerRepo->addNew($containerListEntries, $webstore->pluginSetId);
        }
    }

    /**
     * @param int $webstoreId
     * @param string $containerKey
     * @param string $dataProviderKey
     * @return array
     */
    private function createContainerDataListEntry($webstoreId, $containerKey, $dataProviderKey)
    {
        $webstore = $this->getWebstore($webstoreId);
        $NetsEasyPayPlugin = $this->getNetsEasyPayPlugin($webstoreId);
        $ceresPlugin = $this->getCeresPlugin($webstoreId);

        $dataListEntry = [];

        $dataListEntry['containerKey'] = $containerKey;
        $dataListEntry['dataProviderKey'] = $dataProviderKey;
        $dataListEntry['dataProviderPluginId'] = $NetsEasyPayPlugin->id;
        $dataListEntry['containerPluginId'] = $ceresPlugin->id;
        $dataListEntry['pluginSetId'] = $webstore->pluginSetId;
        $dataListEntry['dataProviderPluginSetEntryId'] = $NetsEasyPayPlugin->pluginSetEntries->firstWhere('pluginSetId', $webstore->pluginSetId)->id;
        $dataListEntry['containerPluginSetEntryId'] = $ceresPlugin->pluginSetEntries->firstWhere('pluginSetId', $webstore->pluginSetId)->id;

        return $dataListEntry;
    }

    /**
     * @param int $webstoreId
     * @return Plugin
     */
    private function getCeresPlugin($webstoreId)
    {
        if ($this->ceresPlugin === null) {
            $webstore = $this->getWebstore($webstoreId);
            $pluginSet = $webstore->pluginSet;
            $plugins = $pluginSet->plugins();
            $this->ceresPlugin = $plugins->where('name', 'Ceres')->first();
        }

        return $this->ceresPlugin;
    }

    /**
     * @param int $webstoreId
     * @return Plugin
     */
    private function getNetsEasyPayPlugin($webstoreId)
    {
        if ($this->NetsEasyPayPlugin === null) {
            $webstore = $this->getWebstore($webstoreId);
            $pluginSet = $webstore->pluginSet;
            $plugins = $pluginSet->plugins();
            $this->NetsEasyPayPlugin = $plugins->where('name', 'NetsEasyPay')->first();
        }

        return $this->NetsEasyPayPlugin;
    }

    /**
     * Check if a string is a valid UUID.
     *
     * @param string $string
     * @return false|int
     */
    public static function isValidUUIDv4($string)
    {
        $regex = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
        return preg_match($regex, $string);
    }

    /**
     * Activate the legacy payment method. This is needed to use the NetsEasyPay payment method.
     */
    private function activateLegacyPaymentMethod()
    {
        $paymentMethodRepository = pluginApp(PaymentMethodRepositoryContract::class);
        
        $mopIds = NetsEasyPayHelper::getAllNetsEasyPayMopIds();
        foreach ($mopIds as  $mopId) {
            $paymentMethodRepository->activatePaymentMethod($mopId);
        }
    }
}
