<?php
class Vps_Model_Proxy_Row extends Vps_Model_Row_Abstract
{
    protected $_row;

    public function __construct(array $config)
    {
        $this->_row = $config['row'];
        parent::__construct($config);
    }

    public function getProxiedRow()
    {
        return $this->_row;
    }

    public function __isset($name)
    {
        if ($this->_row->hasColumn($name)) {
            return true;
        } else {
            return parent::__isset($name);
        }
    }

    public function __unset($name)
    {
        if ($this->_row->hasColumn($name)) {
            $this->_row->__unset($name);
        } else {
            parent::__unset($name);
        }
    }

    public function __get($name)
    {
        if ($this->_row->hasColumn($name)) {
            return $this->_row->$name;
        } else {
            return parent::__get($name);
        }
    }

    public function __set($name, $value)
    {
        if ($this->_row->hasColumn($name)) {
            $this->_row->$name = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    public function save()
    {
        $this->_beforeSave();
        $id = $this->{$this->_getPrimaryKey()};
        if (!$id) {
            $this->_beforeInsert();
        } else {
            $this->_beforeUpdate();
        }
        $this->_beforeSaveSiblingMaster();
        $ret = $this->_row->save();
        $this->_afterSave();
        if (!$id) {
            $this->_afterInsert();
        } else {
            $this->_afterUpdate();
        }
        parent::save();
        return $ret;
    }

    public function delete()
    {
        parent::delete();
        $this->_beforeDelete();
        $this->_row->delete();
        $this->_afterDelete();
    }

    public function toArray()
    {
        $ret = $this->_row->toArray();
        foreach ($this->_getSiblingRows() as $row) {
            $ret = array_merge($row->toArray(), $ret);
        }
        return $ret;
    }
}
