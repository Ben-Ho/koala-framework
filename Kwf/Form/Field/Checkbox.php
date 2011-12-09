<?php
/**
 * @package Form
 */
class Kwf_Form_Field_Checkbox extends Kwf_Form_Field_SimpleAbstract
{
    public function __construct($field_name = null, $field_label = null)
    {
        parent::__construct($field_name, $field_label);
        $this->setXtype('checkbox');
        $this->setEmptyMessage(trlKwfStatic("Please mark the checkbox"));
    }

    /**
     * @deprecated
     */
    public function setErrorText()
    {
        throw new Kwf_Exception('setErrorText is deprecated, use setEmptyText');
    }

    protected function _getTrlProperties()
    {
        $ret = parent::_getTrlProperties();
        $ret[] = 'boxLabel';
        return $ret;
    }

    protected function _addValidators()
    {
        parent::_addValidators();

        if ($this->getAllowBlank() === false
            || $this->getAllowBlank() === 0
            || $this->getAllowBlank() === '0'
        ) {
            $v = new Kwf_Validate_NotEmptyNotZero();
            if ($this->getEmptyMessage()) {
                $v->setMessage(Kwf_Validate_NotEmpty::IS_EMPTY, $this->getEmptyMessage());
            }
            $this->addValidator($v, 'notEmpty');
        }
    }

    public function getTemplateVars($values, $fieldNamePostfix = '')
    {
        $name = $this->getFieldName();
        $value = isset($values[$name]) ? $values[$name] : $this->getDefaultValue();

        $ret = parent::getTemplateVars($values);
        //todo: escapen
        $ret['id'] = str_replace(array('[', ']'), array('_', '_'), $name.$fieldNamePostfix);
        $ret['html'] = "<input type=\"checkbox\" id=\"$ret[id]\" name=\"$name$fieldNamePostfix\" ";
        if ($value) $ret['html'] .= 'checked="checked" ';
        $ret['html'] .= "/>";
        if ($this->getBoxLabel()) {
            $ret['html'] .= ' <label class="boxLabel" for="'.$ret['id'].'">'.$this->getBoxLabel().'</label>';
        }
        $ret['html'] .= "<input type=\"hidden\" name=\"$name$fieldNamePostfix-post\" value=\"1\" />";
        return $ret;
    }

    public function processInput($row, $postData)
    {
        $fieldName = $this->getFieldName();
        if (isset($postData[$fieldName.'-post'])) {
            $postData[$fieldName] = (int)isset($postData[$fieldName]);
        }
        return $postData;
    }
}
