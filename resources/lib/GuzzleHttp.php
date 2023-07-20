<?php


use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

$client = new \GuzzleHttp\Client();

$API_ENDPOINT = 'https://api.dibspayment.eu/v1';

$Payload   = SdkRestApi::getParam('Payload');
$Method    = SdkRestApi::getParam('Method');
$Token     = SdkRestApi::getParam('Token');
$TestMode  = SdkRestApi::getParam('TestMode');

if($TestMode){
    $API_ENDPOINT = 'https://test.api.dibspayment.eu/v1';
}


$params = [
    'headers' => [
        'Accept' => 'application/json',
        'Authorization' => $Token,
        'Content-type' => 'application/json'
    ]
];

switch ($Method) {
    
    case 'CreatePaymentId':

        $URL = $API_ENDPOINT.'/payments/';
        $params['body'] = json_encode($Payload['Paymentdata']);

        return sendRequest($client,'POST',$URL,$params);

    case 'getNetsEasyPaymentByID':

       $URL = $API_ENDPOINT.'/payments/'.$Payload['PaymentId'];
       return sendRequest($client,'GET',$URL,$params);

    case 'UpdatedPaymentId':
        
        $URL = $API_ENDPOINT.'/payments/'.$Payload['PaymentId'].'/orderitems';
        $params['body'] = json_encode($Payload['updatedData']);

        return sendRequest($client,'PUT',$URL,$params);

    case 'ChargePayment' : 

        $URL = $API_ENDPOINT.'/payments/'.$Payload['PaymentId'].'/charges';
        $params['body'] = json_encode($Payload['ChargeData']);
    
        return sendRequest($client,'POST',$URL,$params);
    
    case 'CancelPayment' : 
        $URL = $API_ENDPOINT.'/payments/'.$Payload['PaymentId'].'/cancels';
        $params['body'] = json_encode($Payload['CancelData']);
    
         return sendRequest($client,'POST',$URL,$params);

    case 'RefundPayment' : 
       
        $URL = $API_ENDPOINT.'/charges/'.$Payload['chargeId'].'/refunds';
        $params['body'] = json_encode($Payload['RefundData']);
    
        return sendRequest($client,'POST',$URL,$params);

    case 'UpdateNetsEasyPaymentRef':
        $URL = $API_ENDPOINT.'/payments/'.$Payload['PaymentId'].'/referenceinformation';
        $params['body'] = json_encode($Payload['ReferenceData']); 

        return sendRequest($client,'PUT',$URL,$params);

        

}












function sendRequest($client,$method,$url,$params){
    
    try {
        
        $response = $client->request($method, $url,$params);

        return [
            'statusCode' => $response->getStatusCode(),
            'data' =>json_decode($response->getBody(), true)
        ];
    
    
    } catch (RequestException $ex) {
    
          return [
                   'statusCode' => $ex->getResponse()->getStatusCode(),
                   'error' => 'error : '. $ex->getResponse()->getBody()->getContents()
                ];

    }

}