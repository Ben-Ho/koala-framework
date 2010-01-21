<?php
class Vps_Form_Field_NumberField extends Vps_Form_Field_TextField
{
    public function __construct($field_name = null, $field_label = null)
    {
        parent::__construct($field_name, $field_label);
        $this->setXtype('numberfield');
        $this->setDecimalSeparator(trlcVps('decimal separator', '.'));
    }
    protected function _addValidators()
    {
        parent::_addValidators();

        if ($this->getMaxValue()) {
            $this->addValidator(new Zend_Validate_LessThan($this->getMaxValue()+0.000001));
        }
        if ($this->getMinValue()) {
            $this->addValidator(new Zend_Validate_GreaterThan($this->getMinValue()-0.000001));
        }
        if ($this->getAllowNegative() === false) {
            $this->addValidator(new Zend_Validate_GreaterThan(-1));
        }
        if ($this->getAllowDecimals() === false) {
            $this->addValidator(new Vps_Validate_Digits(true));
        } else {
            $l = null;
            if (trlcVps('locale', 'C') != 'C') {
                $l = Zend_Locale::findLocale(trlcVps('locale', 'C'));
            }
            $this->addValidator(new Zend_Validate_Float($l));
        }
    }

    protected function _getValueFromPostData($postData)
    {
        $fieldName = $this->getFieldName();
        if (!isset($postData[$fieldName])) $postData[$fieldName] = null;
        if ($postData[$fieldName] == ''
            && !(is_int($postData[$fieldName]) && $postData[$fieldName] === 0)
        ) {
            $postData[$fieldName] = null;
        }
        return $postData[$fieldName];
    }

    public static function getSettings()
    {
        return array_merge(parent::getSettings(), array(
            'componentName' => trlVps('Number Field')
        ));
    }
}
