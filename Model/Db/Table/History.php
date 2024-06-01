<?php

class Listcheckin_Model_Db_Table_History extends Core_Model_Db_Table {
    protected $_name                    = "listcheckin_history";
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
                "attendance_date",
                "startdate",
                "enddate",
                "startlatlong",
                "endlatlong",
                "totaltime",
                "startaddress",
                "endaddress",
                "status",
                "location_id",
                "customer_id",
                "phone_number",
                "personal_id",
                "note",
                "total_member",
                "created_at",
                "updated_at"
            ]);

          $select->where("main.value_id = ?", $value_id); 
          $select->where("main.status != ?", 3);        

          $select->joinLeft(['c' => 'customer'], 'c.customer_id = main.customer_id', ['c.firstname', 'c.lastname', 'c.email', 'c.mobile']);

          $select->joinLeft(['l' => 'listcheckin_locations'], 'l.id = main.location_id', ['l.name as location_name', 'l.address']);    
          
          if (array_key_exists("limit", $params) && array_key_exists("offset", $params)) {
	            $select->limit($params["limit"], $params["offset"]);
	        }

          if (array_key_exists("location_id", $params) && !empty($params["location_id"])) {
             $select->where("main.location_id = ?", $params['location_id']); 
          }

          if (array_key_exists("customer_id", $params)) {
             $select->where("main.customer_id = ?", $params["customer_id"]); 
          }

	        if (array_key_exists("filter", $params)) {
	            $select->where("(c.firstname LIKE ? OR c.lastname LIKE ? OR c.nickname LIKE ? OR c.email LIKE ? OR main.phone_number LIKE ? OR l.name LIKE ?)", "%" . $params["filter"] . "%");
	        }

          if (array_key_exists("startdate", $params) && !empty($params["startdate"])) {
                $select->where("startdate >= ?",  $params["startdate"].' 00:00:00');
          }

          if (array_key_exists("enddate", $params) && !empty($params["enddate"])) {
                $select->where("enddate <= ?",  $params["enddate"].' 23:59:59');
          }

          if (array_key_exists("sorts", $params) && !empty($params["sorts"])) {
	            $orders = [];
	            foreach ($params["sorts"] as $key => $dir) {
	                $order = ($dir == -1) ? "DESC" : "ASC";
	                if($key == 'name'){
	                	$orders = "c.{$key} {$order}";
	                }else{
	                	$orders = "main.{$key} {$order}";
	                }
	            }	           
	            $select->order($orders);
	        } else {
	            $select->order('main.id DESC');
	        }

        return $this->_db->fetchAll($select);

   }

    /**
     * @param $value_id
     */
    public function countAllForApp($value_id, $params = [])
    {
        $select =$this->_db->select()
            ->from(['main' => $this->_name], [ 
            	 'COUNT(main.id)'
                ])
            ->where('main.value_id = ?', $value_id);

          $select->where("main.status != ?", 3);

          $select->joinLeft(['c' => 'customer'], 'c.customer_id = main.customer_id', ['c.firstname', 'c.lastname', 'c.email']);

          $select->joinLeft(['l' => 'listcheckin_locations'], 'l.id = main.location_id', ['l.name as location_name', 'l.address']); 

          if (array_key_exists("location_id", $params) && !empty($params["location_id"])) {
             $select->where("main.location_id = ?", $params['location_id']); 
          }

          if (array_key_exists("customer_id", $params)) {
             $select->where("main.customer_id = ?", $params["customer_id"]); 
          }

          if (array_key_exists("filter", $params)) {
              $select->where("(c.firstname LIKE ? OR c.lastname LIKE ? OR c.nickname LIKE ? OR c.email LIKE ? OR main.phone_number LIKE ? OR l.name LIKE ?)", "%" . $params["filter"] . "%");
          }   

          if (array_key_exists("startdate", $params) && !empty($params["startdate"])) {
                $select->where("startdate >= ?",  $params["startdate"].' 00:00:00');
          }

          if (array_key_exists("enddate", $params) && !empty($params["enddate"])) {
                $select->where("enddate <= ?",  $params["enddate"].' 23:59:59');
          }

          if (array_key_exists("check", $params)) {
             if($params['check'] == 'in'){
                $select->where("main.status = ?", 1);   
             }
             if($params['check'] == 'out'){
                $select->where("main.status = ?", 2);   
             }
          }
            
          //$select->where("main.status != ?", 2); 
 
 
        return $this->_db->fetchCol($select);
    }

    /**
     * @param $value_id
     */
    public function getCustomerLastRecords($customer_id, $params = [])
    {
        $select = $this->_db->select()
            ->from(['main' => $this->_name], [
                "id",
                "customer_id",
                "phone_number",
                "personal_id",
                "created_at",
                "updated_at"
            ]);

          $select->where("main.customer_id = ?", $customer_id); 
          $select->order('main.id DESC');

        return $this->_db->fetchRow($select);
    }
}