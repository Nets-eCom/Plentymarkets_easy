<?php

namespace NetsEasyPay\Migrations;

use NetsEasyPay\Models\AccessToken;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;
use NetsEasyPay\Services\WebHookHandler;

class Create_AccessToken_1_0_3
{
    /**
     * Create the AccessToken table
    */
    public function run(Migrate $migrate)
    {

        $migrate->createTable(AccessToken::class);

         WebHookHandler::generate_New_token();
        
    }
}