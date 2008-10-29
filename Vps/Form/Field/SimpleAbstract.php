<?php
abstract class Vps_Form_Field_SimpleAbstract extends Vps_Form_Field_Abstract
{
    public function load($row, $postData = array())
    {
        $ret = array();
        if (isset($postData[$this->getFieldName()])) {
            $ret[$this->getFieldName()] = $postData[$this->getFieldName()];
        } else {
            if ($this->getSave() !== false && $this->getInternalSave() !== false) {
                $ret[$this->getFieldName()] = $this->getData()->load($row);
            }
        }
        if (!isset($ret[$this->getFieldName()]) || is_null($ret[$this->getFieldName()])) {
            $ret[$this->getFieldName()] = $this->getDefaultValue();
        }
        return array_merge($ret, parent::load($row, $postData));
    }

    protected function _addValidators()
    {
        parent::_addValidators();
    }

    public function validate($row, $postData)
    {
        $ret = parent::validate($row, $postData);

        if ($this->getInternalSave() !== false) {

            $data = $this->_getValueFromPostData($postData);

            $name = $this->getFieldLabel();
            if (!$name) $name = $this->getName();
            if ($this->getAllowBlank() === false
                || $this->getAllowBlank() === 0
                || $this->getAllowBlank() === '0') {
                $v = new Vps_Validate_NotEmpty();
                if (!$v->isValid($data)) {
                    $ret[] = $name.": ".implode("<br />\n", $v->getMessages());
                }
            }
            if ($data) {
                foreach ($this->getValidators() as $v) {
                    if ($v instanceof Vps_Validate_Row_Abstract) {
                        $v->setField($this->getName());
                        $isValid = $v->isValidRow($data, $row);
                    } else {
                        $isValid = $v->isValid($data);
                    }
                    if (!$isValid) {
                        $ret[] = $name.": ".implode("<br />\n", $v->getMessages());
                    }
                }
            }
        }
        return $ret;
    }

    public function prepareSave(Vps_Model_Row_Interface $row, $postData)
    {
        parent::prepareSave($row, $postData);
        if ($this->getSave() !== false && $this->getInternalSave() !== false) {
            $data = $this->_getValueFromPostData($postData);
            $this->getData()->save($row, $data);
        }
    }

    protected function _getValueFromPostData($postData)
    {
        $fieldName = $this->getFieldName();
        if (!isset($postData[$fieldName])) $postData[$fieldName] = null;
        return $postData[$fieldName];
    }
}
