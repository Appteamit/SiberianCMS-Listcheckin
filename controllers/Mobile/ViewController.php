<?php

use Siberian\Exception;

/**
 * Class Listcheckin_Mobile_ViewController
 */
class Listcheckin_Mobile_ViewController extends Application_Controller_Mobile_Default
{

    /**
     * Fetch all settings
     *
     */
    public function findallAction()
    {
        try {
        
        if($value_id = $this->getRequest()->getParam('value_id')){
            $param = $this->getRequest()->getBodyParams();
            $customerId = (integer) $this->_getCustomerId(true);
            $is_checked_in = false;
            $trackInfo = [];
            $history = [];
            $location = [];

            $timetracking = (new Listcheckin_Model_History())->find(array('customer_id' => $customerId, 'status' => 1));
            $trackInfo = $timetracking->getData();

            if(!empty($trackInfo)){
                $trackInfo['tracking_id'] = (integer) $trackInfo['id']; 
                $trackInfo['startdate'] = date('Y-m-d H:i:s',strtotime($trackInfo['startdate']));
                $trackInfo['starttime'] = date('g:i A',  strtotime($trackInfo['startdate']));
                $is_checked_in = true;

                $locationModel = (new Listcheckin_Model_Locations())
                    ->find(['id' => $trackInfo['location_id']]);
                $locationInfo = $locationModel->getData();

            }

             /* Settings */
            $settingsModel = (new Listcheckin_Model_Listcheckin())
                                    ->find($value_id, "value_id");
            $sv =  $settingsModel->getData();
            $settings = ['check_in_history_enable' => (integer) $sv['check_in_history_enable'],
                        'required_note' => (boolean) $sv['required_note'],
                        'required_phone_number' => (boolean) $sv['required_phone_number'],
                        'qr_checkin_note' => (string) $sv['qr_checkin_note'],
                        'required_personal_id' => (boolean) $sv['required_personal_id'],
                        'is_checkout_enable' => (boolean) $sv['is_checkout_enable'],
                        'location_tracking' => (boolean) $sv['location_tracking']
                    ];

            $hParam = ['customer_id' => $customerId];
            $history = (new Listcheckin_Model_History())
                                    ->findAllForApp($value_id, $hParam);

            $history_collection = [];
            foreach ($history as $data) {
                $data["date"] = date("F jS, Y" , strtotime($data["startdate"]));
                $data["location_name"] = !empty($data['location_name']) ? $data['location_name'] : '';
                $data["starttime"] = date('g:i A' , strtotime($data["startdate"]));
                $data["endtime"] = !empty($data["enddate"]) ?  date('g:i A' , strtotime($data["cenddate"])) : '-';  
                if(!empty($data["enddate"])) {
                    $seconds = strtotime($data["enddate"]) - strtotime($data["startdate"]);  
                    $total_seconds = $total_seconds + $seconds;
                    $days    = floor($seconds / 86400);
                    $hours   = floor(($seconds - ($days * 86400)) / 3600);
                    $minutes = floor(($seconds - ($days * 86400) - ($hours * 3600))/60);
                    if($hours > 0){
                        $data['total_hours'] =  $hours." Hrs ".$minutes ." Mins";
                    }elseif($minutes > 0){
                        $data['total_hours'] =  $minutes ." Mins";
                    }else{
                        $data['total_hours'] =  $seconds ." Sec";
                    }
                   
                }else{
                    $data['total_hours'] = p__('listcheckin', 'Checked-In');
                }
                $history_collection[] = $data;
            }

            $settings['isManager'] = false;
            $settings['locationId'] = 0;
            $manager = (new Listcheckin_Model_Manager())
                    ->find(['customer_id' => $customerId]);

            if($manager->getId()){
                $settings['isManager'] = true;
                $settings['locationId'] = (integer) $manager->getLocationId(); 
            }

            $payload = [
                    'success' => true,
                    'page_title' => (string) $this->getCurrentOptionValue()->getTabbarName(),                     
                    'is_checked_in' => $is_checked_in,
                    'timetracking' => $trackInfo,
                    'history' => array_values($history_collection),
                    'settings' => $settings,
                    'locationInfo' => $locationInfo      
               ];             
        }

        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }
        
        $this->_sendJson($payload);
    }


     /**
     * Fetch all settings
     *
     */
    public function fetchSettingsAction()
    {
        try {
        
        if($value_id = $this->getRequest()->getParam('value_id')){
           
            /* Settings */
            $settingsModel = (new Listcheckin_Model_Listcheckin())
                                    ->find($value_id, "value_id");
            $sv =  $settingsModel->getData();
            $settings = ['check_in_history_enable' => (integer) $sv['check_in_history_enable'],
                        'required_note' => (boolean) $sv['required_note'],
                        'required_phone_number' => (boolean) $sv['required_phone_number'],
                        'qr_checkin_note' => (string) $sv['qr_checkin_note'],
                        'required_personal_id' => (boolean) $sv['required_personal_id'],
                        'is_checkout_enable' => (boolean) $sv['is_checkout_enable'],
                        'location_tracking' => (boolean) $sv['location_tracking']
                    ];

 
            $payload = [
                    'success' => true,
                    'settings' => $settings 
               ];             
        }

        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }
        
        $this->_sendJson($payload);
    }

     public function fetchManagerBookingAction()
    {
        try {


            $request = $this->getRequest();
            $limit = $request->getParam("perPage", 50);
            $offset = $request->getParam("offset", 0); 
            $value_id = $this->getRequest()->getParam('value_id'); 
            $location_id = $this->getRequest()->getParam('location_id');
            $tab = $this->getRequest()->getParam('tab'); 

            $filter = null;
            $startdate = null;
            $enddate = null;
 
            if ($tab == 'today') {            
                  $startdate = date('Y-m-d');
                //  $enddate = date('Y-m-d');
            }

            if ($tab == 'previous') {            
                  $enddate = date('Y-m-d', strtotime('-1 days'));
            }
                     
            $params = [
                "limit" => $limit,
                "offset" => $offset, 
                "filter" => $filter,
                "startdate" => $startdate,
                "enddate" => $enddate,
                "tab" => $tab,
                "location_id" => $location_id
            ];          

            $application = $this->getApplication();
            $customers = (new Listcheckin_Model_History())
                ->findAllForApp($value_id, $params);
            $countFiltered =   (new Listcheckin_Model_History())->countAllForApp($value_id, $params);

            $customersJson = [];
            foreach ($customers as $customer) {
                $customer['name'] = $customer["firstname"]. ' '.$customer["lastname"];
                $customer["totaltime"] = !empty($customer['totaltime']) ? $customer['totaltime'] :  p__("lischeckin", "Running");
                $customer["location_name"] = !empty($customer['location_name']) ? $customer['location_name'] : '';
                $customer['totaltime'] = !empty($customer['enddate']) ? $customer['totaltime'] :  p__("listcheckin", "Checked-In") ;
                $customer['enddate'] = !empty($customer['enddate']) ? $customer['enddate'] :  "-" ;
        
                $customersJson[] = $customer;
            }

            $params = [
                "startdate" => $startdate,
                "enddate" => $enddate,
                "tab" => $tab,
                "location_id" => $location_id,
                "check" => 'in'
            ];

            $countCheckIn =   (new Listcheckin_Model_History())->countAllForApp($value_id, $params);

            $params['check'] = 'out';
            $countCheckOut =   (new Listcheckin_Model_History())->countAllForApp($value_id, $params);



            $payload = [
                'success' => true,
                'collection' => $customersJson,
                'countFiltered' => $countFiltered[0],
                'countCheckIn' => $countCheckIn[0],
                'countCheckOut' => $countCheckOut[0],
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
     * @param $latitude
     * @param $longitude
     * @return array
     */
    public static function geoReverse($latitude, $longitude, $apiKey = null)
    {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $latitude .
            ',' . $longitude . '&sensor=true&key=' . $apiKey;
        $decode = Siberian_Json::decode(file_get_contents($url));

        $locality = '';
        $postal_code = '';
        $country = '';
        $country_code = '';
        $addresscomponents = [];
        $result = $decode['results'][0];
        $address_components = $result['address_components'];
        foreach ($address_components as $address_component) {
            $addresscomponents[] = $address_component;

            $type = $address_component['types'][0];
            if ($type === 'locality') {
                $locality = $address_component['long_name'];
            }
            if ($type === 'postal_code') {
                $postal_code = $address_component['long_name'];
            }
            if ($type === 'country') {
                $country = $address_component['long_name'];
                $country_code = $address_component['short_name'];
            }
        }

        return [
            'locality' => $locality,
            'postal_code' => $postal_code,
            'country' => $country,
            'country_code' => $country_code,
            'address' => $result['formatted_address']
        ];
    }


   /**
     *@return mixed|null
     */
    public function verifyScanAction() {
        $payload = [];
        
        try{ 

        if($location_value = $this->getRequest()->getParam('location_id')) { 
            $value_id = $this->getRequest()->getParam('value_id');
            $customerId = (integer) $this->_getCustomerId(true);
            
            $location_array = explode("-", $location_value);
            $location_id = $location_array[0];
            $actionType = $location_array[1];

            $locationModel = (new Listcheckin_Model_Locations())
                    ->find(['id' => $location_id, 'value_id' => $value_id]);

            if($locationModel->getStatus() != 1){
                  throw new Exception(p__('listcheckin', 'This shop is not Available for Check-In!'));
            }

            if($locationModel->getId()){

                $timetracking = (new Listcheckin_Model_History())->find(array('customer_id' => $customerId, 'status' => 1));
                    
                    if($actionType == "checkout" && !$timetracking->getId()){
                        throw new Exception(p__('listcheckin', 'Sorry you have not checked-In %s!', $locationModel->getName()));
                    }

                    if($actionType == "checkout" && $timetracking->getLocationId() != $location_id){
                        throw new Exception(p__('listcheckin', 'Sorry you have not checked-In in %s!', $locationModel->getName()));
                    }

                    if($timetracking->getId() && $actionType == "checkin"){
                        throw new Exception(p__('listcheckin', 'You have already checked-In in %s!', $locationModel->getName()));

                    } 

                    if($locationModel->getAllowScan() == 'restricted'){
                       $lcModel = (new Listcheckin_Model_LocationsCustomers())
                            ->find(['customer_id' => $customerId, 'location_id' => $location_id]); 
                        if($lcModel->getId()){
                            $locationInfo = $locationModel->getData();
                            $locationInfo['allow_more_member'] = (boolean) $locationInfo['allow_more_member'];

                            $payload = [
                                'success' => true,
                                'is_valid' => 1,
                                'locationInfo' => $locationInfo,
                                'messages' => p__('listcheckin', 'Scan Successfully')
                               ]; 
                        }else{
                            $payload = [
                                "error" => true,
                                'is_valid' => 0,
                                "message" => p__('listcheckin', 'You may not have the permission to check-In!')
                            ];
                        }

                    }else{
                        $locationInfo = $locationModel->getData();
                        $locationInfo['allow_more_member'] = (boolean) $locationInfo['allow_more_member'];
                        
                        $payload = [
                            'success' => true,
                            'is_valid' => 1,
                            'locationInfo' => $locationInfo,
                            'messages' => p__('listcheckin', 'Scan Successfully')                                       
                        ]; 
                    }
 
            } else{
                $payload = [
                    "error" => true,
                    'is_valid' => 0,
                    "message" => p__('listcheckin', 'Invalid QR code!')
                ];
            }

        }else{
            $payload = [
                "error" => true,
                'is_valid' => 0,
                "message" => p__('listcheckin', 'Invalid Param!')
            ];
        }

        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

       /**
     *@return mixed|null
     */
    public function verifyWebScanAction() {
        $payload = [];
        
        try{ 

        if($location_value = $this->getRequest()->getParam('location_id')) { 
            $value_id = $this->getRequest()->getParam('value_id');
            $customerId = (integer) $this->_getCustomerId(false);
            
            $location_array = explode("-", $location_value);
            $location_id = $location_array[0];
            $actionType = $location_array[1];

            $settingsModel = (new Listcheckin_Model_Listcheckin())
                                    ->find($value_id, "value_id");
            $sv =  $settingsModel->getData();
            $settings = ['check_in_history_enable' => (integer) $sv['check_in_history_enable'],
                        'required_note' => (boolean) $sv['required_note'],
                        'required_phone_number' => (boolean) $sv['required_phone_number'],
                        'qr_checkin_note' => (string) $sv['qr_checkin_note'],
                        'required_personal_id' => (boolean) $sv['required_personal_id']
                    ];

            $locationModel = (new Listcheckin_Model_Locations())
                    ->find(['id' => $location_id, 'value_id' => $value_id]);

            if($locationModel->getStatus() != 1){
                  throw new Exception(p__('listcheckin', 'This shop is not Available for Check-In!'));
            }

            if($locationModel->getId()){
                $is_checked_in = false;
                $trackInfo = [];
                
                if($customerId > 0) {
                    $timetracking = (new Listcheckin_Model_History())->find(array('customer_id' => $customerId, 'status' => 1));
                    $trackInfo = $timetracking->getData();

                    if(!empty($trackInfo)){
                        $trackInfo['tracking_id'] = (integer) $trackInfo['id']; 
                        $trackInfo['startdate'] = date('Y-m-d H:i:s',strtotime($trackInfo['startdate']));
                        $trackInfo['starttime'] = date('g:i A',  strtotime($trackInfo['startdate']));
                        $is_checked_in = true;
                    }else{
                     $getOldInfo = (new Listcheckin_Model_History())->getCustomerLastRecords($customerId);
                        $oldInfo = [];
                        if(!empty($getOldInfo)){
                            $oldInfo['personal_id'] = $getOldInfo['personal_id']; 
                            $oldInfo['phone_number'] = $getOldInfo['phone_number'];
                        }
                    }
                }            
                
                $payload = [
                    'success' => true,
                    'is_valid' => 1,
                    'locationInfo' => $locationModel->getData(),
                    'settings' => $settings,
                    'is_checked_in' => $is_checked_in,
                    'page_title' => (string) $this->getCurrentOptionValue()->getTabbarName(),
                    'timetracking' => $trackInfo,
                    'messages' => p__('listcheckin', 'Scan Successfully'),
                    'oldInfo' => $oldInfo                                      
                ];              
 
            } else{
                $payload = [
                    "error" => true,
                    'is_valid' => 0,
                    "message" => p__('listcheckin', 'Invalid QR code!')
                ];
            }

        }else{
            $payload = [
                "error" => true,
                'is_valid' => 0,
                "message" => p__('listcheckin', 'Invalid Param!')
            ];
        }

        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }
    
         /**
     * Save Start
     *
     */
    public function saveStartAction()
    {
       try {

        if($param = $this->getRequest()->getBodyParams()){
            /* Settings */
            $settingsModel = (new Listcheckin_Model_Listcheckin())
                                    ->find($param['value_id'], "value_id");
             /* Settings */
            $settingsModel = (new Listcheckin_Model_Listcheckin())
                                    ->find($param['value_id'], "value_id");
           
            if((boolean)$settingsModel->getRequiredPhoneNumber() && empty($param['phone_number'])){
                throw new Exception(p__('listcheckin', 'Please enter a Phone Number!'));
            }

            if((boolean)$settingsModel->getRequiredPersonalId() && empty($param['personal_id'])){
                throw new Exception(p__('listcheckin', 'Please enter a Personal ID'));
            }             

            $customerId = $this->_getCustomerId(true);
            $now = new Zend_Date(strtotime($param['startdate']), false, new Zend_Locale('en_US'));
            $today =  $now->toString('y-MM-dd HH:mm:ss');
            $lat_long = $param['latitude'].','.$param['longitude'];

            $address = $this->geoReverse($param['latitude'], $param['longitude'] , $this->getApplication()->getGooglemapsKey());

            $location_id = empty($param['location_id']) ? 0 : $param['location_id'];
            $note = empty($param['note']) ? "" : $param['note'];
            $phone_number = empty($param['phone_number']) ? "" : $param['phone_number'];
            $personal_id = empty($param['personal_id']) ? "" : $param['personal_id'];
            $is_checked_in  = false;

            if(!empty($customerId)){
               $Timetracking = (new Listcheckin_Model_History())
                ->setValueId($param['value_id'])
                ->setCustomerId($customerId)
                ->setStartdate($today)
                ->setAttendanceDate($now->toString('y-MM-dd'))
                ->setStartlatlong($lat_long)
                ->setLocationId($location_id)
                ->setNote($note)
                ->setPhoneNumber($phone_number)
                ->setTotalMember($param['total_member'])
                ->setStartaddress($address['address'])
                ->setPersonalId($personal_id);
               $Timetracking->save();

                $trackInfo = [];
                if($Timetracking->getId()){
                    $is_checked_in = true;
                    $trackInfo['tracking_id'] = (integer) $Timetracking->getId(); 
                    $trackInfo['startdate']=date('Y-m-d H:i:s',strtotime($param['startdate']));
                    $trackInfo['starttime'] = date('g:i A',  strtotime($param['startdate']));
                    $locationModel = (new Listcheckin_Model_Locations())
                    ->find(['id' => $location_id]);
                    $locationInfo = $locationModel->getData();

                    if(!(boolean) $settingsModel->getIsCheckoutEnable()){
                            $Timetracking = (new Listcheckin_Model_History())
                                ->find(['id' => $Timetracking->getId()])
                                ->setEnddate($today)
                                ->setEndlatlong($lat_long)
                                ->setTotaltime('00:00:00')
                                ->setStatus(2)
                                ->setEndaddress($address['address'])
                                ->save();
                            $is_checked_in = false;
                    }
                }

                $payload = [
                    'success' => true,
                    'message' => p__('listcheckin', 'You have been Check-In successfully!'),                  
                    'tracking_id' =>  (integer) $Timetracking->getId(),
                    'timetracking' => $trackInfo,
                    'locationInfo' => $locationInfo,
                    'is_checked_in' => (boolean) $is_checked_in                   
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

    /**
     * Save End
     *
     */
    public function saveEndAction()
    {
        try {

        if($param = $this->getRequest()->getBodyParams()){
            
            $now = new Zend_Date(strtotime($param['enddate']), false, new Zend_Locale('en_US'));
            $today =  $now->toString('y-MM-dd HH:mm:ss');
            $lat_long = $param['latitude'].','.$param['longitude'];

            $address = $this->geoReverse($param['latitude'], $param['longitude'] , $this->getApplication()->getGooglemapsKey());
           
            if(!empty($param['tracking_id'])){

               $Timetracking = (new Listcheckin_Model_History())
                ->find(['id' => $param['tracking_id']])
                ->setEnddate($today)
                ->setEndlatlong($lat_long)
                ->setTotaltime($param['totaltime'])
                ->setStatus(2)
                ->setEndaddress($address['address'])
                ->save();

                $payload = [
                    'success' => true,
                    'message' => p__('listcheckin', 'Save Successfully!'),                  
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


     private function getPdf($data) {

        // Define the font styles
        $font_regular = Zend_Pdf_Font::fontWithPath(Zend_Pdf_Font::FONT_DEJAVUSANS);
        $font_bold = Zend_Pdf_Font::fontWithPath(Zend_Pdf_Font::FONT_DEJAVUSANS_BOLD);

        // Create a blank PDF, define the color's lines and the ordinate
        $pdf = new Siberian_Pdf();
        $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
        $page->setLineColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
        $pdf->pages[] = $page;
        $y = 760;
        
        // Columns
        $page->setFont($font_bold, 10);
        $page->drawText(p__('listcheckin', "Name"), 50, $y);
        $page->drawText(p__('listcheckin', "Email"), 150, $y);
        $page->drawText(p__('listcheckin', "Mobile"), 275, $y);
        $page->drawText(p__('listcheckin', "Date"), 375, $y);
        $page->drawText(p__('listcheckin', "Check-In"), 500, $y);$y-=10;
        $page->drawLine(50, $y, 550, $y);$y-=1;
        $page->drawLine(50, $y, 550, $y);$y-=15;


        foreach ($data as $line) {  
            $line["date"] = date("F jS, Y" , strtotime($line["startdate"]));
            $line["location_name"] = !empty($line['location_name']) ? $line['location_name'] : '';
            $line["starttime"] = date('g:i A' , strtotime($line["startdate"]));
            $line["endtime"] = !empty($line["enddate"]) ?  date('g:i A' , strtotime($line["cenddate"])) : '-';  

            $y_ref = $y;            
            $page->setFont($font_regular, 11);              
            $page->drawText($line['firstname'].' '.$line['lastname'], 50, $y_ref)
                ->drawText($line['email'], 150, $y)
                ->drawText($line['phone_number'], 275, $y)                
                ->drawText($line['date'], 375, $y)
                ->drawText($line['starttime'], 500, $y)
            ;
 
        }

        return $pdf;
    }

     /**
     *
     */
    public function exportCsvAction() {
        
      if ($this->getApplication()->getId()) {

        try {
            $value_id = $this->getRequest()->getParam('value_id'); 
            $location_id = $this->getRequest()->getParam('location_id');
            $tab = $this->getRequest()->getParam('tab'); 

            $filter = null;
            $startdate = null;
            $enddate = null;
            $dateRangeTitle = "";
            if ($tab == 'today') {            
                  $startdate = date('Y-m-d');
                  $dateRangeTitle = $startdate;
            }

            if ($tab == 'previous') {            
                  $enddate = date('Y-m-d', strtotime('-1 days'));
                  $dateRangeTitle = $dateRangeTitle.' - '.$enddate;

            }
                     
            $params = [                
                "startdate" => $startdate,
                "enddate" => $enddate,
                "tab" => $tab,
                "location_id" => $location_id
            ];          
 
            $customer_id = $this->_getCustomerId(true);

            $reports = (new Listcheckin_Model_History())
                ->findAllForApp($value_id, $params);

            $customer = $this->getSession()->getCustomer();

         
            $mail = new Siberian_Mail();
           // $mail->setFrom($customer->getEmail(), $this->getApplication()->getShortName());
            $mail->addTo($customer->getEmail(),  $customer->getFirstname());
            $mail->setSubject(p__('listcheckin', "Report %s - %s", $this->getApplication()->getShortName(), $startdate ));
            $mail->setBodyHtml("");
            $mail->createAttachment(
                $this->getPdf($reports)->render(),
                Zend_Mime::TYPE_OCTETSTREAM,
                Zend_Mime::DISPOSITION_ATTACHMENT,
                Zend_Mime::ENCODING_BASE64,
                "report.pdf"
            );
            $mail->send();  
            

                $payload = [
                    'success' => true,
                    'message' => p__('listcheckin', 'Mail send Successfully!'),                  
               ];  
           
            } catch (Exception $e) {
                $payload = [
                    "error" => true,
                    "message" => $e->getMessage()
                ];
            }
        }

         $this->_sendJson($payload);
    }

    /**
     * Save End
     *
     */
    public function checkoutManualAction()
    {
        try {

        if($request = $this->getRequest()) {
            $tracking_id = $request->getParam("tracking_id", null);

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


       /**
     * @param bool $throw
     * @return mixed|null
     * @throws Exception
     * @throws Zend_Session_Exception
     */
    private function _getCustomerId($throw = true)
    {
        $request = $this->getRequest();
        $session = $this->getSession();
        $customerId = $session->getCustomerId();
        if ($throw && empty($customerId)) {
            throw new Exception(p__('listcheckin', 'Customer login required!'));
        }
        return $customerId;
    }

}
