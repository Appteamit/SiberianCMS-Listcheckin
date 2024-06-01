<?php

class Listcheckin_Form_Listcheckin extends Siberian_Form_Abstract
{
    
    public function init() {
        parent::init();
        
        $this
            ->setAction(__path("/listcheckin/application/editpost"))
            ->setAttrib("id", "form-add-listcheckin");
        
        self::addClass("create", $this); 

        $this->addSimpleHidden("id");
        $value_id = $this->addSimpleHidden("value_id");
        $value_id->setRequired(true);

       /* $this->addSimpleText('admin_email', p__('listcheckin', 'Admin Email (For Notification)'))->setRequired(true);
*/ 
       $this->addSimpleSelect(
            "is_checkout_enable",
            p__("lischeckin", "Checkout Required?"),
            [ 
                "1" =>  p__("attendance", "Yes"),
                "0" =>  p__("attendance", "No")                
            ])->setRequired(true);

        $this->addSimpleCheckbox('location_tracking', p__('listcheckin', 'Location tracking Enable?'));

        $this->addSimpleCheckbox('check_in_history_enable', p__('listcheckin', 'Check In/Out History Display?'));

       $this->addSimpleCheckbox('required_phone_number', p__('listcheckin', 'Required Phone Number?'));

        $this->addSimpleCheckbox('required_personal_id', p__('listcheckin', 'Required Personal Id?'));

        $this->addSimpleCheckbox('required_note', p__('listcheckin', 'Required Customer Note?'));

        $qr_checkin_note = $this->addSimpleTextarea('qr_checkin_note', p__('listcheckin','QR check-In Message'));
        $qr_checkin_note->setRichtext();

 
    }
    
    public function setElementValueById($id, $value, $required = false) {
        $element = $this->getElement($id)->setValue($value);
        if( $required ) {
            $element->setRequired(true);
        }
    }
 
}