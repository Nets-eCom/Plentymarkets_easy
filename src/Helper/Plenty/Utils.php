<?php

namespace NetsEasyPay\Helper\Plenty;


use Plenty\Modules\Property\V2\Contracts\PropertyRepositoryContract;
use Plenty\Modules\Order\Property\Contracts\OrderItemPropertyRepositoryContract;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Property\V2\Contracts\PropertyGroupRepositoryContract;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Order\Property\Contracts\OrderPropertyRepositoryContract;

use NetsEasyPay\Configuration\PluginConfiguration;

class Utils
{



    public static function CreateInitialProperty($names) {

        $status = [];

        foreach ($names as $name) {
            
            $OrderPropertyType = self::GetOrderPropertyType($name) ;

            $Isexist = $OrderPropertyType ? true : false;
            
            if(!$Isexist){
                self::CreateOrderPropertyType($name);
                $status[$name] = 'created';
            }else{
                $status[$name] = 'already exist';
            }

        }

        return $status;
       
      
    }
    public  static function GetOrderPropertyType($name = null){
        
        $OrderPropertyTypes =  pluginApp(AuthHelper::class)->processUnguarded(
            function () {
                return pluginApp(OrderPropertyRepositoryContract::class)->getTypes(['de']);
            });
        
        if($name){
            foreach ($OrderPropertyTypes as $key => $OrderPropertyType) {
                if($OrderPropertyType->names[0]->name == $name){
                     return  $OrderPropertyType;
                }
            }

            return null;
        }

        return $OrderPropertyTypes;

    }
    public  static function CreateOrderPropertyType($name){

       $data =  [
                    "cast"=> "string",
                    "names"=> [[
            
                        "name"=> $name,
                        "lang"=> "de"
                    ]],
                    "lang"=> $name,
                    "name"=> "de"
                ];

        return pluginApp(AuthHelper::class)->processUnguarded(
                 function () use ($data){
                    return  pluginApp(OrderPropertyRepositoryContract::class)
                            ->createType($data);
                 });


    }
    
    
    public  static function CreateProperty($names_data)
    {

        $groupe =  self::getGroupeByName(PluginConfiguration::PAYMENT_KEY_EASY);
        $groupe = (sizeof($groupe) == 0) ? self::CreatePropertygroupe(PluginConfiguration::PAYMENT_KEY_EASY) : $groupe[0];
        
        $data     = [
            'cast'           => "string",
            'position'       => 0,
            'type' => 'item',
            'names'          => $names_data,
            "groups" => [
                [
                    'id' => $groupe->id
                ]
            ]
        ];
    

        $type =  pluginApp(AuthHelper::class)->processUnguarded(function () use ($data){
            return  pluginApp(PropertyRepositoryContract::class)->create($data);
        });
    
        return $type;
    }
    public  static function Isexist($name){

        $properties =  self::GetPropertyByName($name);
        $found = null;
        foreach ($properties as $key => $property) {
           if($property->names[0]->name == $name){
               $found  = $property ;
               break;
           }
        }
         
        return $found;

    }
    public  static function Getallprops(){

        $name = '';
        $properties =  pluginApp(AuthHelper::class)->processUnguarded
          (function () use ($name){
                return pluginApp(PropertyRepositoryContract::class)->search(
                                $with = ['names'],
                                $perPage = 1000, 
                                $page = 1, 
                                $sorting = []
                            );
           
        });

        return  $properties;
  
    }
    public  static function GetPropertyByType($type){

        $properties = pluginApp(AuthHelper::class)->processUnguarded
          (function () use ($type){
               pluginApp(PropertyRepositoryContract::class)->setFilters([
                  'type' => $type,
                ]);
                return pluginApp(PropertyRepositoryContract::class)->search(
                                $with = ['names'],
                                $perPage = 1000, 
                                $page = 1, 
                                $sorting = []
                            );
           
        });

        return  $properties;
  
    }
    public  static function  GetPropertyByName($name){

        $properties =  pluginApp(AuthHelper::class)->processUnguarded
          (function () use ($name){
               pluginApp(PropertyRepositoryContract::class)->setFilters([
                  'type' => 'item',
                  'name' => $name
                ]);
                return pluginApp(PropertyRepositoryContract::class)->search(
                                $with = ['names'],
                                $perPage = 1000, 
                                $page = 1, 
                                $sorting = []
                            );
           
        });

        return  $properties;
  
    }
    public  static function setOrderProperty($PropertyId,$OrderId,$Value){
        
        pluginApp(OrderRepositoryContract::class)->updateOrder([
            'properties' =>  [
              [
                  'typeId' => $PropertyId,
                  'value' =>  (string) $Value
              ]
             ]
          ],$OrderId);
    }
    public  static function SetOrderItemProperty($PropertyId,$OrderItemId,$Value){
        

        $results =  pluginApp(AuthHelper::class)->processUnguarded(


        function () use ($PropertyId,$OrderItemId,$Value){

            return pluginApp(OrderItemPropertyRepositoryContract::class)->create(
            [
                'typeId' =>  $PropertyId,
                'value' => (string) $Value,
                'orderItemId'=> intval($OrderItemId)
            ]);
        });
      
        return $results;
    
  
    }


    public  function GetPropertyById($id){
        

        return pluginApp(AuthHelper::class)->processUnguarded(
                    function () use ($id) {
                        return  pluginApp(PropertyRepositoryContract::class)->get($id,[]);
             });
  
    }
    public  function GetPropertygroupeById($groupeId){
  

        $groupe =  pluginApp(AuthHelper::class)->processUnguarded(function () use ($groupeId){
            return PropertyGroup::findPropertyGroup($groupeId);
        });
      
        return $groupe;
        
      
    }
    public  function CreatePropertygroupe(string $groupKey) {
        

        $groupe =  pluginApp(AuthHelper::class)->processUnguarded(
            function () use ($groupKey){
                    
                $createdGroup = pluginApp(PropertyGroupRepositoryContract::class)->create([
                    "position" => 0,
                    'names'          => [
                        [
                            'lang'        => 'en',
                            'name'        => $groupKey,
                        ],
                        [
                            'lang'        => 'de',
                            'name'        => $groupKey,
                        ]
                    ]
                ]);

                return $createdGroup;
        });

        return $groupe;
    
    }
    public  static function getGroupeByName($name){
        
        $groupe =  pluginApp(AuthHelper::class)->processUnguarded
          (function () use ($name){
               pluginApp(PropertyGroupRepositoryContract::class)->setFilters([
                  'name' => $name
                ]);
                return pluginApp(PropertyGroupRepositoryContract::class)->search(
                                $with = ['names'],
                                $perPage = 1000, 
                                $page = 1, 
                                $sorting = []
                            );
           
        });

        return  $groupe;
    }



}
