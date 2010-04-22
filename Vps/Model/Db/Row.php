<?php
class Vps_Model_Db_Row extends Vps_Model_Row_Abstract
{
    protected $_row;

    public function __construct(array $config)
    {
        $this->_row = $config['row'];
        parent::__construct($config);
    }

    function __clone()
    {
        // Force a copy of this->object, otherwise
        // it will point to same object.
        $this->_row = clone $this->_row;
    }

    public function __isset($name)
    {
        $n = $this->_transformColumnName($name);
        if (isset($this->_row->$n)) return true;
        return parent::__isset($name);
    }

    public function __unset($name)
    {
        $n = $this->_transformColumnName($name);
        if (isset($this->_row->$n)) {
            unset($this->_row->$n);
        } else {
            parent::__unset($name);
        }
    }

    public function __get($name)
    {
        $n = $this->_transformColumnName($name);
        if (isset($this->_row->$n)) {
            $value = $this->_row->$n;
            if (is_string($value) && substr($value, 0, 13) =='vpsSerialized') {
                $value = unserialize(substr($value, 13));
            }
            return $value;
        } else {
            return parent::__get($name);
        }
    }

    public function __set($name, $value)
    {
        $n = $this->_transformColumnName($name);
        if (isset($this->_row->$n)) {
            if (is_array($value) || is_object($value)) {
                $value = 'vpsSerialized'.serialize($value);
            }
            if ($this->$name !== $value) {
                $this->_dirty = true;
            }
            $this->_row->$n = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    public function save()
    {
        $insert =
            !is_array($this->_getPrimaryKey())
            && !$this->{$this->_getPrimaryKey()};
        if ($insert) {
            $this->_beforeInsert();
        } else {
            $this->_beforeUpdate();
        }
        $this->_beforeSaveSiblingMaster();
        $this->_beforeSave();
        if ($insert || $this->_dirty) {
            $ret = $this->_row->save();
            $this->_dirty = false;
        } else {
            $ret = $this->{$this->_getPrimaryKey()};
        }
        if ($insert) {
            $this->_afterInsert();
            $this->_model->afterInsert($this);
        } else {
            $this->_afterUpdate();
        }
        $this->_afterSave();

        parent::save(); //siblings nach uns speichern; damit auto-inc id vorhanden
        return $ret;
    }

    public function delete()
    {
        parent::delete();
        $this->_beforeDelete();
        $this->_row->delete();
        $this->_afterDelete();
    }

    public function toDebug()
    {
        return $this->_row->toDebug();
    }
    public function __toString()
    {
        if ($this->_model->getToStringField()) {
            return $this->{$this->_model->getToStringField()};
        }
        return $this->_row->__toString();
    }

    public function getRow()
    {
        return $this->_row;
    }

    public function toArray()
    {
        $ret = parent::toArray();
        foreach ($this->_model->getOwnColumns() as $c) {
            $ret[$c] = $this->$c;
        }
        return $ret;
    }

    public function findDependentRowset($dependentTable, $ruleKey = null, Vps_Model_Select $select = null)
    {
        $dbSelect = $this->_model->createDbSelect($select);
        if ($dependentTable instanceof Vps_Model_Db) {
            $dependentTable = $dependentTable->getTable();
        }
        return $this->_row->findDependentRowset($dependentTable, $ruleKey, $dbSelect);
    }

    public function findParentRow($parentTable, $ruleKey = null, Vps_Model_Select $select = null)
    {
        $dbSelect = $this->_model->createDbSelect($select);
        if ($parentTable instanceof Vps_Model_Db) {
            $parentTable = $parentTable->getTable();
        }
        $class = get_class($this);
        return $this->_row->findParentRow($parentTable, $ruleKey, $dbSelect);
    }

    public function findManyToManyRowset($matchTable, $intersectionTable, $callerRefRule = null,
                                         $matchRefRule = null, Vps_Model_Select $select = null)
    {
        $dbSelect = $this->_model->createDbSelect($select);
        if ($matchTable instanceof Vps_Model_Db) {
            $matchTable = $matchTable->getTable();
        }
        if ($intersectionTable instanceof Vps_Model_Db) {
            $intersectionTable = $intersectionTable->getTable();
        }
        return $this->_row->findManyToManyRowset($matchModel, $intersectionModel, $callerRefRule, $matchRefRule, $dbSelect);
    }
}
