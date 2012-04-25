<?php
class Vpc_Guestbook_FormController extends Vps_Controller_Action_Auto_Form
{
    protected $_buttons = array();
    protected $_permissions = array('save', 'add');

    public function _initFields()
    {
        $this->_form->setModel(Vpc_Abstract::createModel($this->_getParam('class')));

        $this->_form->add(new Vps_Form_Field_ShowField('create_time', trlVps('Created')));
        $this->_form->add(new Vps_Form_Field_Checkbox('visible', trlVps('Visible')));
        $this->_form->add(new Vps_Form_Field_TextField('name', trlVps('Name')))
            ->setWidth(300);
        $this->_form->add(new Vps_Form_Field_TextField('email', trlVps('E-Mail')))
            ->setWidth(300);
        $this->_form->add(new Vps_Form_Field_TextArea('content', trlVps('Content')))
            ->setWidth(300)
            ->setHeight(160);
    }

    public function _beforeSave($row)
    {
        if ($this->_getParam('id') == 0 &&
            $this->_getParam('componentId') &&
            $row->getModel()->hasColumn('component_id')
        ) {
            if (isset($row->component_id)) $row->component_id = $this->_getParam('componentId');
        }
    }
}
