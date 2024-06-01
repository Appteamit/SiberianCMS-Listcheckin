<?php

/**
 * Class Listcheckin_ReportsController
 */
class Listcheckin_ReportsController extends Application_Controller_Default
{

    /**
     * Leavs list
     */
    public function listAction() {
        $this->loadPartials();
    }
    

    public function fetchCustomersAction()
    {
        try {
            $request = $this->getRequest();
            $limit = $request->getParam("perPage", 25);
            $offset = $request->getParam("offset", 0);
            $sorts = $request->getParam("sorts", []);
            $queries = $request->getParam("queries", []);

            $filter = null;
            $startdate = null;
            $enddate = null;
            $location_id = null;

            if (array_key_exists("search", $queries)) {
                $filter = $queries["search"];
            }
            if (array_key_exists("from", $queries)) {
                $startdate = date('Y-m-d', strtotime($queries["from"]));
            }
            if (array_key_exists("to", $queries)) {
                $enddate = date('Y-m-d', strtotime($queries["to"]));
            }
            if (array_key_exists("location_id", $queries)) {
                $location_id = $queries["location_id"];
            }

            
            $params = [
                "limit" => $limit,
                "offset" => $offset,
                "sorts" => $sorts,
                "filter" => $filter,
                "startdate" => $startdate,
                "enddate" => $enddate,
                "location_id" => $location_id
            ];
            
            $value_id = (new Listcheckin_Model_Listcheckin())->getCurrentValueId();

            $application = $this->getApplication();
            $customers = (new Listcheckin_Model_History())
                ->findAllForApp($value_id, $params);

            $countAll = (new Listcheckin_Model_History())->countAllForApp($value_id);
            $countFiltered =   (new Listcheckin_Model_History())->countAllForApp($value_id, $params);

          
            $customersJson = [];
            foreach ($customers as $customer) {
                $customer['name'] = $customer["firstname"]. ' '.$customer["lastname"];
                $customer["totaltime"] = !empty($customer['totaltime']) ? $customer['totaltime'] :  p__("lischeckin", "Running");
                $customer["location_name"] = !empty($customer['location_name']) ? $customer['location_name'] : '';
                $customer['totaltime'] = !empty($customer['enddate']) ? $customer['totaltime'] :  p__("listcheckin", "Checked-In") ;
                $customer['enddate'] = !empty($customer['enddate']) ? $customer['enddate'] :  "-" ;
                $customer['phone_number'] = !empty($customer['phone_number']) ? $customer['phone_number'] : $customer['mobile'];
                $customersJson[] = $customer;
            }

            $payload = [
                "records" => $customersJson,
                "queryRecordCount" => $countFiltered[0],
                "totalRecordCount" => $countAll[0]
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }


      /**
     *
     */
    public function exportCsvAction()
    {
      if ($this->getApplication()->getId()) {

        try {
              
            $request = $this->getRequest();
            $queries = $request->getParam("queries", []);

            $filter = $request->getParam("search", null);
            $startdate =  $request->getParam("from", null);
            $enddate =  $request->getParam("to", null);

           $params = [
                "filter" => $filter,
                "startdate" => !empty($startdate) ? date('Y-m-d', strtotime($startdate)) : null,
                "enddate" =>  !empty($enddate) ? date('Y-m-d', strtotime($enddate)) : null,
            ];
  
            $value_id = (new Listcheckin_Model_Listcheckin())->getCurrentValueId();            
            $customers = (new Listcheckin_Model_History())
                ->findAllForApp($value_id, $params);
      
           $csv_string = "FirstName,LastName,E-Mail,Location,Start Date,End Date,total,Start Address,End Address,Phone Number,Personal ID,Note\n";

           foreach ($customers as $customer) {                
                $startaddress = str_replace(',', ' ', $customer['startaddress']);
                $endaddress = str_replace(',', ' ', $customer['endaddress']);                
                $location = !empty($customer['location_name']) ? $customer['location_name'] : '';                 
                $enddate = $customer["enddate"];

                $customer['phone_number'] = !empty($customer['phone_number']) ? $customer['phone_number'] : $customer['mobile'];

                $csv_string .= $customer['firstname'].",".$customer['lastname'].",".$customer['email'].",".$location.",".$customer["startdate"].",".$enddate.",".$customer['totaltime'].",".$startaddress.",".$endaddress.",".$customer['phone_number'].",".$customer['personal_id'].",".$customer['note']."\n";                
           }

            $date = date("Y-m-d_H-i-s");
            $filename = "checkin_report_".$date.".csv";
            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            echo $csv_string;
            exit();
           
            } catch (Exception $e) {
                if(APPLICATION_ENV === "development") {
                    Zend_Debug::dump($e);
                }
                return false;
            }
        }
    }


    public function deleteNewAction() {
        try {
            $request = $this->getRequest();
            $application = $this->getApplication();
            $customerId = $request->getParam("id", null);

            $customer = (new Listcheckin_Model_History())
                ->find($customerId);
            if (!$customer->getId()) {
                throw new \Siberian\Exception("#07888-01" . p__("listcheckin", "We are unable to delete this time slot!"));
            }

            $customer->delete();

            $payload = [
                'success' => true,
                'message' => p__("listcheckin", "Saved successfully") ,
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

   /**
     * Save End
     *
     */
    public function checkoutAction()
    {
        try {

        if($request = $this->getRequest()) {
            $tracking_id = $request->getParam("id", null);

            $now = new Zend_Date();
            $today =  $now->toString('y-MM-dd HH:mm:ss');
             
            if(!empty($tracking_id)){

               $Timetracking = (new Listcheckin_Model_History())
                ->find(['id' => $tracking_id])
                ->setEnddate($today)               
                ->setTotaltime('Manual')
                ->setStatus(2)
                ->save();

                $payload = [
                    'success' => true,
                    'message' => p__('listcheckin', 'Check-Out Successfully!'),                  
               ];  
            }else{
                $payload = [
                    "error" => true,
                    "message" =>  p__('listcheckin', 'Id must be required!'),
                ];
            }
        }

        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }
        $this->_sendJson($payload);
    }


 
}