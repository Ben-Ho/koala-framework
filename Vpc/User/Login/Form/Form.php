<?php
class Vpc_User_Login_Form_Form extends Vps_Form
{
    protected function _init()
    {
        parent::_init();
        $this->_model = new Vps_Model_FnF();

        $this->add(new Vps_Form_Field_TextField('email', trlVps('E-Mail')))
                    ->setVType('email');

        $this->add(new Vps_Form_Field_Password('password', trlVps('Password')));

        $this->add(new Vps_Form_Field_Checkbox('auto_login', trlVps('Auto Login')));
    }
}
