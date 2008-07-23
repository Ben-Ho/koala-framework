<?php
abstract class Vps_Form_Field_Abstract extends Vps_Component_Abstract implements Vps_Collection_Item_Interface
{
    private $_properties;
    protected $_validators = array();
    private $_data;

    public function __construct($fieldName = null, $fieldLabel = null)
    {
        if ($fieldName) $this->setName($fieldName);
        if ($fieldLabel) $this->setFieldLabel($fieldLabel);
        $this->setLabelSeparator(':');
        $this->_init();
    }

    protected function _init()
    {
    }

    public function __call($method, $arguments)
    {
        if (substr($method, 0, 3) == 'set') {
            if (!isset($arguments[0]) && !is_null($arguments[0])) {
                throw new Vps_Exception("Missing argument 1 (value)");
            }
            $name = strtolower(substr($method, 3, 1)) . substr($method, 4);
            return $this->setProperty($name, $arguments[0]);
        } else if (substr($method, 0, 3) == 'get') {
            $name = strtolower(substr($method, 3, 1)) . substr($method, 4);
            return $this->getProperty($name);
        } else {
            throw new Vps_Exception("Invalid method called: '$method'");
        }
    }

    public function setProperty($name, $value)
    {
        if (is_null($value)) {
            unset($this->_properties[$name]);
        } else {
            $this->_properties[$name] = $value;
        }
        return $this;
    }

    public function getProperty($name)
    {
        if (isset($this->_properties[$name])) {
            return $this->_properties[$name];
        } else {
            return null;
        }
    }

    public function setProperties(array $properties)
    {
        foreach ($properties as $k=>$value) {
            if (is_string($k) && $k != 'id') {
                $fn = 'set'.str_replace(' ','',ucwords(str_replace('_', ' ', $k)));
                call_user_func(array($this, $fn), $value);
            }
        }
    }

    public function getMetaData()
    {
        $ret = $this->_properties;
        if (isset($ret['name'])) {
            $ret['name'] = $this->getFieldName();
        }
        if (isset($ret['hiddenName']) && $this->getNamePrefix()) {
            $ret['hiddenName'] = $this->getNamePrefix() . '_' . $ret['hiddenName'];
        }
        if (isset($ret['namePrefix'])) unset($ret['namePrefix']);
        if (isset($ret['save'])) unset($ret['save']);
        return $ret;
    }

    public function load($row)
    {
        $ret = array();
        if ($this->hasChildren()) {
            foreach ($this->getChildren() as $field) {
                $ret = array_merge($ret, $field->load($row));
            }
        }
        return $ret;
    }

    public function processInput($postData)
    {
        if ($this->hasChildren()) {
            foreach ($this->getChildren() as $field) {
                $field->processInput($postData);
            }
        }
    }

    public function validate($postData)
    {
        $this->_addValidators();

        $ret = array();
        if ($this->hasChildren()) {
            foreach ($this->getChildren() as $field) {
                $ret = array_merge($ret, $field->validate($postData));
            }
        }
        return $ret;
    }

    public function prepareSave($row, $postData)
    {
        $this->_addValidators();

        if ($this->hasChildren()) {
            foreach ($this->getChildren() as $field) {
                $field->prepareSave($row, $postData);
            }
        }
    }

    public function save($row, $postData)
    {
        if ($this->hasChildren()) {
            foreach ($this->getChildren() as $field) {
                $field->save($row, $postData);
            }
        }
    }

    public function delete(Vps_Model_Row_Interface $row)
    {
        if ($this->hasChildren()) {
            foreach ($this->getChildren() as $field) {
                $field->delete($row);
            }
        }
    }

    public function getName()
    {
        if (isset($this->_properties['name'])) {
            return $this->getProperty('name');
        } else if (isset($this->_properties['hiddenName'])) {
            return $this->getHiddenName();
        } else {
            return null;
        }
    }

    public function getFieldName()
    {
        $ret = $this->getName();
        if ($this->getNamePrefix()) {
            $ret = $this->getNamePrefix() . '_' . $ret;
        }
        return $ret;
    }

    public function getByName($name)
    {
        if ($this->getName() == $name) {
            return $this;
        } else {
            return null;
        }
    }

    public function hasChildren()
    {
        return false;
    }

    public function getChildren()
    {
        return array();
    }

    public function getValidators()
    {
        return $this->_validators;
    }
    public function addValidator(Zend_Validate_Interface $v)
    {
        $this->_validators[] = $v;
    }

    /**
     * Fügt die Standard-Validatoren für dieses Feld hinzu.
     * wird aufgerufen in prepareSave
    **/
    protected function _addValidators()
    {
    }

    public function getData()
    {
        if (!isset($this->_data)) {
            $this->setData(new Vps_Data_Table());
        }
        return $this->_data;
    }

    public function setData(Vps_Data_Interface $data)
    {
        $this->_data = $data;
        $data->setFieldname($this->getName());
        return $this;
    }
    public function getTemplateVars($values)
    {
        $ret = array();
        $ret['item'] = $this;
        return $ret;
    }

    public static function getSettings()
    {
        return array_merge(parent::getSettings(), array(
            'componentIcon' => new Vps_Asset('textfield')
        ));
    }

    public function toDebug($indent=0)
    {
        $ind = str_repeat(' ', $indent*4);
        $ret = '';
        $ret .= '<pre style="margin:0">';
        $ret .= "$ind<strong>".get_class($this)."</strong>";
        $c = get_class($this);
        while ($c = get_parent_class($c)) {
            $ret .= " -&gt; $c";
        }
        $ret .= "\n";
        foreach ($this->_properties as $n=>$v) {
            if (is_object($v)) {
                if (method_exists($v, '__toString')) {
                    $v = $v->__toString();
                } else {
                    $v = "(object) ".get_class($v);
                }
            }
            $ret .= "$ind  $n: $v\n";
        }
        $children = '';
        if ($this->hasChildren()) {
            foreach ($this->getChildren() as $field) {
                $children .= $field->toDebug($indent+1);
            }
        }
        if ($this->hasChildren()) {
            $ret .= "{$ind}childs(".count($this->getChildren())."):\n";
        }
        $ret .= '</pre>';
        $ret .= $children;
        return $ret;
    }
}
