<?php

class Listcheckin_Model_Listcheckin extends Core_Model_Default {

    /**
     * @var null
     */
    public static $acl = null;

    /**
     * @var string
     */
    protected $_db_table = Listcheckin_Model_Db_Table_Listcheckin::class;

     /**
     * @param $value_id
     * @return array|bool
     */
    public function getInappStates($value_id)
    {
        
        $inAppStates = [
            [
                "state" => "listcheckin-home",
                "offline" => false,
                "params" => [
                    "value_id" => $value_id,
                ]             
            ],
        ];

        return $inAppStates;
    }

    /**
     * @return null
     */
    public static function getCurrentValueId()
    {
        $app = self::getApplication();
        if ($app) {
            $options = $app->getOptions();
            foreach ($options as $option) {
                if ($option->getCode() === "listcheckin") {
                    return $option->getId();
                }
            }
        }
        return null;
    }

    /**
     * @return null
     */
    public static function getCurrent()
    {
        $app = self::getApplication();
        if ($app) {
            $options = $app->getOptions();
            foreach ($options as $option) {
                if ($option->getCode() === "listcheckin") {
                    return $option;
                }
            }
        }
        return null;
    }
  
     
     
}