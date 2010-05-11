<?php
class Vps_Form_Field_DateField extends Vps_Form_Field_SimpleAbstract
{
    public function __construct($field_name = null, $field_label = null)
    {
        parent::__construct($field_name, $field_label);
        $this->setXtype('datefield');
    }

    protected function _processLoaded($v)
    {
        if (strlen($v) > 10) {
            $v = substr($v, 0, 10);
        }
        return $v;
    }

    protected function _addValidators()
    {
        parent::_addValidators();
        $this->addValidator(new Zend_Validate_Date());
    }

    protected function _getValueFromPostData($postData)
    {
        $ret = parent::_getValueFromPostData($postData);
        if ($ret == trlVps('yyyy-mm-dd')) $ret = null;
        if ($ret == '') $ret = null;
        if ($ret) {
            $ret = str_replace('"', '', $ret);
            $date = new Vps_Date($ret);
            $ret = $date->get(Zend_Date::YEAR)
                .'-'.$date->get(Zend_Date::MONTH)
                .'-'.$date->get(Zend_Date::DAY);
        }
        return $ret;
    }

    public function getTemplateVars($values, $fieldNamePostfix = '')
    {
        $name = $this->getFieldName();
        $value = $values[$name];
        if (!$value) $value = trlVps('yyyy-mm-dd');
        $ret = parent::getTemplateVars($values, $fieldNamePostfix);

        $value = htmlspecialchars($value);
        $name = htmlspecialchars($name);
        $ret['id'] = str_replace(array('[', ']'), array('_', '_'), $name.$fieldNamePostfix);
        $ret['html'] = "<input type=\"text\" id=\"$ret[id]\" name=\"$name$fieldNamePostfix\" value=\"$value\" style=\"width: {$this->getWidth()}px\" maxlength=\"{$this->getMaxLength()}\"/>";
        return $ret;
    }
}
