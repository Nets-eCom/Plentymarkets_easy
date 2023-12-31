<?php

namespace NetsEasyPay\Services;

use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use Plenty\Modules\Plugin\DataBase\Contracts\Model;

class DatabaseBaseService
{
    /**
     * @var DataBase
     */
    public $dataBase;

    public function __construct(DataBase $dataBase)
    {
        $this->dataBase = $dataBase;
    }

    /**
     * Set the settings value
     *
     * @param Model $model
     *
     * @return mixed
     */
    protected function setValue(Model $model)
    {
        if($model instanceof Model)
        {
            return $this->dataBase->save($model);
        }
        return false;
    }

    /**
     * Delete the give model from the database
     *
     * @param Model $model
     * @return bool
     */
    public function deleteValue($model)
    {
        if($model instanceof Model)
        {
            return $this->dataBase->delete($model);
        }
        return false;
    }

    /**
     * Get the settings value
     *
     * @param string $modelClassName
     * @param mixed $key
     * @return bool|mixed
     */
    public static function getValue($modelClassName, $key)
    {
         
        $dataBase = pluginApp(DataBase::class);

        $result = $dataBase->find($modelClassName, $key);

        if($result)
        {
            return $result;
        }
        return false;
    }

    /**
     * @param string $modelClassName
     * @return bool|array
     */
    public static function getValues($modelClassName, $fields=[], $values=[], $operator=['='])
    {


        $query = pluginApp(DataBase::class)->query($modelClassName);
   
        if( is_array($fields) && is_array($values) &&
            count($fields) > 0 && count($values) && count($values) == count($fields)
        )
        {
            foreach ($fields as $key => $field)
            {
                $query->where($field, array_key_exists($key,$operator)?$operator[$key]:'=', $values[$key]);
            }
        }

        $results = $query->get();

        if($results)
        {
            return $results;
        }
        return false;
    }
}