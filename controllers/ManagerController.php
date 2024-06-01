<?php

/**
 * Class Listcheckin_ManagerController
 */
class Listcheckin_ManagerController extends Application_Controller_Default
{

    /**
     *list
     */
    public function listAction() {
         if ($id = $this->getRequest()->getParam('id')) {
            try {
              
               $model = (new Listcheckin_Model_Locations())
                    ->find(['id' => $id]);
                if (!$model->getId()) {
                        $this->getRequest()->addError( p__("listcheckin",  "This project does not exist."));
                }

                } catch (\Exception $e) {
                   $this->getRequest()->addError( p__("listcheckin",  "Something went wrong."));
            }
        }

        $this->loadPartials();
        $this->getLayout()->getPartial('content')->setLocations($model);
    }
    

    public function fetchManagersAction()
    {
        try {
            $request = $this->getRequest();
            $limit = $request->getParam("perPage", 25);
            $offset = $request->getParam("offset", 0);
            $sorts = $request->getParam("sorts", []);
            $queries = $request->getParam("queries", []);

            $filter = null;
            $location_id = $this->getRequest()->getParam('location_id');;
        
            if (array_key_exists("search", $queries)) {
                $filter = $queries["search"];
            }            
            
            $params = [
                "limit" => $limit,
                "offset" => $offset,
                "sorts" => $sorts,
                "filter" => $filter,
                "location_id" => $location_id
            ];
          
            $value_id = (new Listcheckin_Model_Listcheckin())->getCurrentValueId();

            $customers = (new Listcheckin_Model_Manager())
                ->findAllForApp($value_id, $params);
       
            $customersJson = [];
            foreach ($customers as $customer) {
                $customer['name'] = $customer['firstname'].' '.$customer['lastname'];
                $customersJson[] = $customer;
            }

            $payload = [
                "records" => $customersJson
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
     *Create
     */
    public function createAction() {
          if ($id = $this->getRequest()->getParam('id')) {
            try {
              
               $model = (new Listcheckin_Model_Locations())
                    ->find(['id' => $id]);
                if (!$model->getId()) {
                        $this->getRequest()->addError( p__("listcheckin",  "This project does not exist."));
                }

                } catch (\Exception $e) {
                   $this->getRequest()->addError( p__("listcheckin",  "Something went wrong."));
            }
        }

        $this->loadPartials();
        $this->getLayout()->getPartial('content')->setLocations($model);
    }

    /**
     *Save
     */
    public function saveAction() {
       
        if($param = $this->getRequest()->getPost()) {
            $value_id = (new Listcheckin_Model_Listcheckin())->getCurrentValueId();
            $application = $this->getApplication();
            $appId = $application->getId();

        try {   

                $customerModel = (new Customer_Model_Customer())->find([
                    'email' => $param['email'],
                    'app_id' => $appId
                ]);

                if (!$customerModel->getId()) {
                    
                    $customer = new Customer_Model_Customer();
                    $customer->find($param['email'], 'email');

                    $param['app_id'] = $this->getApplication()->getId();
                    $param['privacy_policy'] = false;
                    $param['communication_agreement'] = false;
                    $customer->setData($param);

                    if (!empty($param['password'])) {
                        $customer->setPassword($param['password']);
                    }
                    $customer->save();
                    $customer_id = $customer->getId();

                }else{
                    $customer_id = $customerModel->getId();
                }

                $model = (new Listcheckin_Model_Manager())
                    ->find(['location_id' => $param['location_id'], 'customer_id' => $customer_id]);

                if($model->getId()){
                     $message =p__('listcheckin', "We are sorry but the %s account is already linked to one of our location", $param["email"]);

                    throw new Exception($message);
                }

                $model->setValueId($value_id)
                    ->setLocationId($param['location_id'])
                    ->setCustomerId($customer_id);
 
                $model->save();

                $payload = [
                    'success' => true,
                    'message' => p__('listcheckin', 'Successfully save')
                ];

            } catch (\Exception $e) {
                $payload = [
                    'error' => true,
                    'message' => $e->getMessage(),
                ];
            }

            $this->_sendJson($payload);
        }
    }

     /**
     *Save
     */
    public function checkEmailAction() {
       
        if($param = $this->getRequest()->getPost()) {
            $application = $this->getApplication();
            $appId = $application->getId();

            try {

                $model = (new Customer_Model_Customer())->find([
                    'email' => $param['email'],
                    'app_id' => $appId
                ]);

                if ($model->getId()) {

                    $payload = [
                        'success' => true,
                        'is_exist' => true,
                        'firstname' => $model->getFirstName(),
                        'lastname' => $model->getLastName(),
                        'customer_id' => (integer) $model->getId()
                    ];

                }else{
                    $payload = [
                        'success' => true,
                        'is_exist' => false
                    ];
                }               

            } catch (\Exception $e) {
                $payload = [
                    'error' => true,
                    'message' => $e->getMessage(),
                ];
            }

            $this->_sendJson($payload);
        }
    }



     /**
     *Remove
     */
    public function removeManagerAction() {
       
         if($customer_id = $this->getRequest()->getParam('customer_id')) {
            $location_id = $this->getRequest()->getParam('location_id');
            
            try {

                $model = (new Listcheckin_Model_Manager())
                    ->find(['location_id' => $location_id, 'customer_id' => $customer_id]);
                $model->delete();                      

                $this->getSession()->addSuccess(p__('listcheckin', "Info deleted saved")); 
                
                $payout = [
                    "success" => 1
                ];
                
            } catch (\Exception $e) {
                $payout = [
                    "error" => 1,
                    "message" => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                ];
            }

            $this->getResponse()->setBody(Zend_Json::encode($payout))->sendResponse();
            die;
        }
    }

}