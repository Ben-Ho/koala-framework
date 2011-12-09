<?php
/**
 * @package Form
 */
class Kwf_Form_Field_EMailField extends Kwf_Form_Field_TextField
{
    public function __construct($field_name = null, $field_label = null)
    {
        parent::__construct($field_name, $field_label);
        $this->setVtype('email');
    }

    protected function _processLoaded($value)
    {
        $value = parent::_processLoaded($value);
        $punycode = new Kwf_Util_Punycode();
        $value = $punycode->decode($value);
        return $value;
    }

    protected function _getValueFromPostData($postData)
    {
        $ret = parent::_getValueFromPostData($postData);
        $punycode = new Kwf_Util_Punycode();
        $ret = $punycode->encode($ret);
        return $ret;
    }
}
