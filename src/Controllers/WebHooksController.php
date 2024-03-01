<?php

namespace NetsEasyPay\Controllers;


use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use NetsEasyPay\Helper\Logger;
use NetsEasyPay\Models\AccessToken;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;
use Plenty\Modules\Authorization\Services\AuthHelper;

use NetsEasyPay\Services\WebHookHandler;

class WebHooksController extends Controller
{

    public function subscribe(Request $request,Response $Response){

        $header = $request->header();
        $payload = $request->all();
        $tokens = AccessToken::where('status', '=', 1) ;

        $access_token = $header['authorization'][0] ?? null;
        $Token =  $tokens[0] ?? null;
        
        $data  = [
            'event_name' => $payload['event'] ,
            'data' => $payload['data'],
            'access_token' => $access_token,
            'token' => $Token,
            'headers' => $header
        ];
        $response = null;

        if($Token && $access_token && $Token->token_value == $access_token){

        Logger::info(__FUNCTION__, 'NetsEasyPay::Debug.WebHooksNotificationAuthorized',$data); 

        $response = pluginApp(AuthHelper::class)->processUnguarded
                    (function () use ($payload){
                        
                        switch ($payload['event']) {
                           // charge event
                            case "payment.charge.created.v2":
                                return WebHookHandler::HandleChargeCreated($payload['data']);
                                break;
            
                            case "payment.charge.failed":
                                return WebHookHandler::HandleChargeFaild($payload['data']);
                                break;
                             // cancel event
                            case "payment.cancel.created":
                                return WebHookHandler::HandleCancelCreated($payload['data']);
                                break;
                            case "payment.cancel.failed":
                                return WebHookHandler::HandleCancelfailed($payload['data']);
                                break;
                             // refund event
                            /*case "payment.refund.initiated.v2":
                                WebHookHandler::HandleRefundInitiated_v2($payloald['data']);
                                break;*/
                            case "payment.refund.initiated":
                                return WebHookHandler::HandleRefundInitiated($payload['data']);
                                break;
                            case "payment.refund.completed":
                                return WebHookHandler::HandleRefundCompleted($payload['data']);
                                break;
                            case "payment.refund.failed":
                                return WebHookHandler::HandleRefundFailed($payload['data']);
                                break; 
                        }
             
                    });
        
            
        }

        if(!$response){
            //return $Response->json($data,Response::HTTP_BAD_REQUEST);
        }

       // if response does not contains error

       Logger::info(__FUNCTION__, 'NetsEasyPay::Debug.WebHooksNotification',[
        'payload' => $data,
        'response' => $response
       ]); 

        return [
                  'payload' => $data,
                  'response' => $response
               ];
        
        
        
        
    }
    public function generate_New_token(){
       
        WebHookHandler::generate_New_token();

    }
    public function get_All_Tokens(){
       
        return AccessToken::all();
 
    }

    public function runMigration(){


        pluginApp(Migrate::class)->deleteTable(AccessToken::class);
        
        pluginApp(Migrate::class)->createTable(AccessToken::class);
        
        WebHookHandler::generate_New_token();
        
        return 'Migration Successfully Done!';
        
    }




    


}