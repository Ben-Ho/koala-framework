<?php
class Kwf_Form_Field_GoogleMapsField extends Kwf_Form_Field_SimpleAbstract
{
    public function __construct($field_name = null, $field_label = null)
    {
        parent::__construct($field_name, $field_label);
        $this->setXtype('googlemapsfield');
    }

    protected function _addValidators()
    {
        parent::_addValidators();
        $this->addValidator(new Zend_Validate_Regex("#^(()|(-?[0-9]+\.[0-9]*;-?[0-9]+\.[0-9]*))$#"));
    }
}
