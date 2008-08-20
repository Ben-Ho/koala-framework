<?php
class Vpc_User_Detail_General_Form extends Vpc_Abstract_Composite_Form
{
    protected $_generalFieldset;
    protected function _init()
    {
        $this->setTable(Zend_Registry::get('userModel'));
        parent::_init();
    }

    protected function _initFields()
    {
        $this->_generalFieldset = $this->add(new Vps_Form_Container_FieldSet(trlVps('General')));
        $this->_generalFieldset->add(new Vps_Form_Field_TextField('email', trlVps('E-Mail')))
                    ->setVType('email')
                    ->setAllowBlank(false)
                    ->setWidth(250);

        $this->_generalFieldset->add(new Vps_Form_Field_TextField('firstname', trlVps('Firstname')))
                    ->setAllowBlank(false)
                    ->setWidth(250);

        $this->_generalFieldset->add(new Vps_Form_Field_TextField('lastname', trlVps('Lastname')))
                    ->setAllowBlank(false)
                    ->setWidth(250);

        $this->_generalFieldset->add(new Vps_Form_Field_TextField('title', trlVps('Title')))
                    ->setWidth(250);

        $this->_generalFieldset->add(new Vps_Form_Field_Select('gender', trlVps('Gender')))
                    ->setValues(array(
                            'female' => trlVps('Female'),
                            'male'   => trlVps('Male')
                    ));
        parent::_initFields();
    }
}
