<?php

/**
 * Class Listcheckin_ApplicationController
 */
class Listcheckin_ApplicationController extends Application_Controller_Default
{
    /**
     *
     */
    public function editAction()
    {
        parent::editAction();
    } 

    /**
     *
     */
    public function editpostAction()
    {
        try
        {
            $values = $this->getRequest()->getPost();
            $form = new  Listcheckin_Form_Listcheckin();
            if ($form->isValid($values))
            {
                $listcheckin = (new Listcheckin_Model_Listcheckin())
                                    ->find($values['value_id'], "value_id");
                $listcheckin->addData($values);
                $listcheckin->save();

                $payload = ["success" => "1", "success_message" => p__("listcheckin", "Saved successfully") , 'message_timeout' => 1, 'message_button' => 0, 'message_loader' => 0, ];              

            }
            else
            {
                /** Do whatever you need when form is not valid */
                $payload = ["error" => true, "message" => $form->getTextErrors() , "errors" => $form->getTextErrors(true) , ];
            }

        }
        catch(\Exception $e)
        {
            $payload = ["error" => true, "message" => $e->getMessage() , ];
        }

        $this->_sendJson($payload);
    }
  
   
}