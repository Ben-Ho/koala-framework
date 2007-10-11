<?php
class Vps_Collection implements ArrayAccess, IteratorAggregate
{
    private $_array = array();

    private $_defaultClass;

    /**
     * @param string Wenn angegeben kann diese Collection nur Klassen von diesem Typ
     *               beinhalten. Falls kein Objekt hinzugefügt wird, so wird ein
     *               Objekt von der hier angegeben Klasse intanziert.
     **/
    public function __construct($defaultClass = null)
    {
        $this->_defaultClass = $defaultClass;
    }

    //ArrayAccess
    public function offsetExists($offset)
    {
        foreach ($this->_array as $v) {
            if ($ret = $v->getByName($name)) {
                return true;
            }
        }
        return false;
    }

    public function getByName($name)
    {
        foreach ($this->_array as $v) {
            if ($ret = $v->getByName($name)) {
                return $ret;
            }
        }
        throw new Vps_Exception("Item '$name' not found.");
    }

    //ArrayAccess
    public function offsetGet($offset)
    {
        return $this->getByName($offset);
    }

    //ArrayAccess
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->add($value);
        } else {
            throw new Vps_Exception("Not yet Implemented.");
        }
    }

    public function add($value = null)
    {
        $value = $this->_preInsertValue($value);
        $this->_array[] = $value;
        $this->_postInsertValue($value);
        return $value;
    }

    //ArrayAccess
    public function offsetUnset($offset)
    {
        throw new Vps_Exception("Not yet Implemented.");
    }

    //IteratorAggregate
    public function getIterator()
    {
        return new Vps_Collection_Iterator($this);
    }

    public function getRecursiveIterator()
    {
        return new RecursiveIteratorIterator(
                        new Vps_Collection_Iterator_Recursive($this));
    }

    public function first()
    {
        if (isset($this->_array[0])) {
            return $this->_array[0];
        } else {
            return null;
        }
    }

    public function prepend($value)
    {
        $value = $this->_preInsertValue($value);
        array_unshift($this->_array, $value);
        $this->_postInsertValue($value);
        return $value;
    }

    public function append($value)
    {
        return $this->add($value);
    }

    public function insertBefore($where, Vps_Collection_Item_Interface $value)
    {
        $value = $this->_preInsertValue($value);
        foreach ($this->_array as $i=>$v) {
            if ($v->getName() == $where) {
                array_splice($this->_array, $i, 0, array($value));
            }
        }
        $this->_postInsertValue($value);
        return $value;
    }

    public function getArray()
    {
        return $this->_array;
    }

    protected function _postInsertValue($value)
    {
    }
    protected function _preInsertValue($value)
    {
        if ($this->_defaultClass && !is_object($value)) {
            $value = new $this->_defaultClass($value);
        }
        if (!$value instanceof Vps_Collection_Item_Interface) {
            throw new Vps_Exception("Vps_Collection can hold only items with Vps_Collection_Item_Interface");
        }
        return $value;
    }

    public function getMetaData()
    {
        $ret = array();
        foreach ($this as $field) {
            $data = $field->getMetaData();
            if (!is_null($data)) $ret[] = $data;
        }
        return $ret;
    }
}
