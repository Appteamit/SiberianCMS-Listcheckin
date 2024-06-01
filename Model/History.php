<?php

class Listcheckin_Model_History extends Core_Model_Default {

    /**
     * @var null
     */
    public static $acl = null;

    /**
     * @var string
     */
    protected $_db_table = Listcheckin_Model_Db_Table_History::class;

    /**
     * @param $datas
     * @return mixed
     */
    public function findAllForApp($value_id, $params = [])
    {
        return $this->getTable()->findAllForApp($value_id, $params);
    }

   /**
     * @param $datas
     * @return mixed
     */
    public function countAllForApp($value_id, $params = [])
    {
        return $this->getTable()->countAllForApp($value_id, $params);
    }

   /**
     * @param $datas
     * @return mixed
     */
    public function getCustomerLastRecords($customer_id, $params = [])
    {
        return $this->getTable()->getCustomerLastRecords($customer_id, $params);
    }

}