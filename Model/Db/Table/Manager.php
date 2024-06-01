<?php

class Listcheckin_Model_Db_Table_Manager extends Core_Model_Db_Table {
    protected $_name                    = "listcheckin_managers";
    protected $_primary                 = "id"; 
    
     /**
     * @param $app_id
     * @param int $limit
     * @return array
     */
    public function findAllForApp($value_id, $params = []) {
        $select = $this->_db->select()
            ->from(['main' => $this->_name], [
                "id",
                "customer_id",
                "location_id",
                "created_at",
                "updated_at"
            ]);

            $select->where("main.value_id = ?", $value_id); 
            $select->where("main.location_id = ?", $params['location_id']);        

            $select->joinLeft(['c' => 'customer'], 'c.customer_id = main.customer_id', ['c.firstname', 'c.lastname', 'c.email']);

            $select->joinLeft(['l' => 'listcheckin_locations'], 'l.id = main.location_id', ['l.name as location_name', 'l.address']);  
           
	        if (array_key_exists("filter", $params)) {
	            $select->where("(c.firstname LIKE ?)", "%" . $params["filter"] . "%");
	        }

 
        return $this->_db->fetchAll($select);

   }

    /**
     * Location Create
     */
    public function createAction() {
        $this->loadPartials();
    }


    
 
}