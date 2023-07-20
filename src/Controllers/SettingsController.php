<?php

namespace NetsEasyPay\Controllers;

use NetsEasyPay\Services\SettingsService;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Response;

class SettingsController extends Controller
{

    public function loadSettings(Response $response)
    {
        return $response->json(SettingsService::getAllSetting());
    }


    


}