<?php

class Listcheckin_Model_Manager extends Core_Model_Default {

    /**
     * @var null
     */
    public static $acl = null;

    /**
     * @var string
     */
    protected $_db_table = Listcheckin_Model_Db_Table_Manager::class;

     /**
     * @param $datas
     * @return mixed
     */
    public function findAllForApp($value_id, $params = [])
    {
        return $this->getTable()->findAllForApp($value_id, $params);
    }

}