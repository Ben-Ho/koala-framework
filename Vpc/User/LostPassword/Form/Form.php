<?php
class Vpc_User_LostPassword_Form_Form extends Vps_Form
{
    protected function _init()
    {
        parent::_init();
        $this->setModel(new Vps_Model_FnF());

        $this->add(new Vpc_User_LostPassword_Form_UserEMail('email', trlVps('E-Mail')))
            ->setAllowBlank(false)
            ->setWidth(200)
            ->setLabelWidth(50);
    }
    protected function _afterSave(Vps_Model_Row_Interface $row)
    {
        Zend_Registry::get('userModel')->lostPassword($row->email);
    }


}
