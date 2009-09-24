<?php
class Vps_Form_Field_SimpleAbstract extends Vps_Form_Field_Abstract
{
    public function load($row, $postData = array())
    {
        $ret = array();
        if (array_key_exists($this->getFieldName(), $postData)) {
            $ret[$this->getFieldName()] = $postData[$this->getFieldName()];
        } else {
            if ($this->getSave() !== false && $this->getInternalSave() !== false) {
                $ret[$this->getFieldName()] = $this->getData()->load($row);
            }
        }
        if (!isset($ret[$this->getFieldName()]) || is_null($ret[$this->getFieldName()])) {
            $ret[$this->getFieldName()] = $this->getDefaultValue();
        }
        $ret[$this->getFieldName()] = $this->_processLoaded($ret[$this->getFieldName()]);
        return array_merge($ret, parent::load($row, $postData));
    }

    protected function _processLoaded($value)
    {
        return $value;
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
                $ret = array_merge($ret, $this->_validateNotAllowBlank($data, $name));
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

    protected function _validateNotAllowBlank($data, $name)
    {
        $ret = array();
        $v = new Vps_Validate_NotEmpty();
        if (!$v->isValid($data)) {
            $ret[] = $name.": ".implode("<br />\n", $v->getMessages());
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
