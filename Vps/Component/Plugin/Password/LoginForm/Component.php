<?php
class Vps_Component_Plugin_Password_LoginForm_Component extends Vpc_Formular_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['childComponentClasses']['success'] = false;
        return $ret;
    }

    protected function _initForm()
    {
        parent::_initForm();
        $this->_form = new Vps_Form();
        $this->_form->setModel(new Vps_Model_FnF());
        $this->_form->add(new Vps_Form_Field_Password('password', trlVps('Password')));
    }
}
