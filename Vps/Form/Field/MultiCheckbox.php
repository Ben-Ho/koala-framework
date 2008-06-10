<?php
//todo: validators
class Vps_Form_Field_MultiCheckbox extends Vps_Form_Field_Abstract
{
    protected $_fields;
    private $_references;
    private $_model;

    public function __construct($tableName = null, $title = null)
    {
        parent::__construct();

        if (is_object($tableName)) {
            $model = $tableName;
        } else if (class_exists($tableName)) {
            $model = new $tableName();
        }
        parent::__construct(get_class($model));
        if ($model instanceof Zend_Db_Table_Abstract) {
            $model = new Vps_Model_Db(array(
                'table' => $model
            ));
        }
        $this->setModel($model);

        if ($title) $this->setTitle($title);
        $this->setHideLabels(true);
        $this->setAutoHeight(true);
        $this->setLayout('form');
        $this->setXtype('fieldset');
    }

    public function setModel($model)
    {
        $this->_model = $model;
    }

    public function getModel()
    {
        return $this->_model;
    }

    public function getMetaData()
    {
        $ret = parent::getMetaData();
        $ret['items'] = $this->_getFields()->getMetaData();
        if (isset($ret['tableName'])) unset($ret['tableName']);
        if (isset($ret['values'])) unset($ret['values']);
        return $ret;
    }

    protected function _getFields()
    {
        if (!isset($this->_fields)) {
            $this->_fields = new Vps_Collection_FormFields();
            $info = $this->getValues()->getTable()->info();
            $pk = $info['primary'][1];
            foreach ($this->getValues() as $i) {
                $k = $i->$pk;
                if (!is_string($i)) $i = $i->__toString();
                $this->_fields->add(new Vps_Form_Field_Checkbox($this->getFieldName()."[$k]"))
                    ->setKey($k)
                    ->setBoxLabel($i);
            }
        }
        return $this->_fields;
    }

    public function hasChildren()
    {
        return sizeof($this->_fields) > 0;
    }
    public function getChildren()
    {
        return $this->_fields;
    }

    public function getName()
    {
        $name = parent::getName();
        if (!$name) {
            $name = $this->getTableName();
        }
        return $name;
    }
    protected function _getRowsByRow(Vps_Model_Row_Interface $row)
    {
        if ($this->_model instanceof Vps_Model_FieldRows) {
            $rows = $this->_model->fetchByParentRow($row);
        } else {
            $pk = $row->getModel()->getPrimaryKey();
            if (!$row->$pk) {
                //neuer eintrag (noch keine id)
                return array();
            }
            $ref = $this->_getReferences($row);
            $where = array();
            foreach (array_keys($ref['columns']) as $k) {
                $where["{$ref['columns'][$k]} = ?"] = $row->{$ref['refColumns'][$k]};
            }
            $rows = $this->_model->fetchAll($where);
        }
        return $rows;
    }
    protected function _getReferences($row)
    {
        if ($this->_references) {
            return $this->_references;
        } else if ($this->_model instanceof Vps_Model_Db && $row instanceof Vps_Model_Db_Row) {
            return $this->_model->getTable()
                        ->getReference(get_class($row->getRow()->getTable()));
        } else {
            throw new Vps_Exception('Couldn\'t read references for Multifields. Either use Vps_Model_FieldRows/Vps_Model_Db or set the References by setReferences().');
        }
    }
    public function setReferences($references)
    {
        $this->_references = $references;
    }
    
    public function load(Vps_Model_Row_Interface $row)
    {
        if (!$row) return array();

        $selected = $this->_getRowsByRow($row);
        $ref = $this->_model->getTable()->getReference(get_class($this->getValues()->getTable()));
        $key = $ref['columns'][0];

        $selectedIds = array();
        foreach ($selected as $i) {
            $selectedIds[] = $i->$key;
        }

        $ret = array();
        foreach ($this->_getFields() as $field) {
            $ret[$field->getFieldName()] = in_array($field->getKey(), $selectedIds);
        }

        return $ret;
    }
    public function save(Vps_Model_Row_Interface $row, $postData)
    {
        $new = array();
        if ($postData[$this->getFieldName()]) {
            foreach ($postData[$this->getFieldName()] as $key=>$value) {
                if ($value) $new[] = $key;
            }
        }
        if ($this->getAllowBlank() === false && $new == array()) {
            throw new Vps_ClientException("Please select at least one ".$this->getTitle().".");
        }
        $saved = $this->_getRowsByRow($row);

        $ref = $this->_getReferences($row);
        $key1 = $ref['columns'][0];
        
        $ref = $this->_model->getTable()->getReference(get_class($this->getValues()->getTable()));
        $key2 = $ref['columns'][0];

        $avaliableKeys = array();
        foreach ($this->_getFields() as $field) {
            $avaliableKeys[] = $field->getKey();
        }

        foreach ($saved as $savedRow) {
            $id = $savedRow->$key2;
            if (in_array($id, $avaliableKeys)) {
                if (!in_array($id, $new)) {
                    $savedRow->delete();
                    continue;
                } else {
                    unset($new[array_search($id, $new)]);
                }
            }
        }

        foreach ($new as $id) {
            if (in_array($id, $avaliableKeys)) {
                $i = $this->_model->createRow();
                $i->$key1 = $row->id;
                $i->$key2 = $id;
                $i->save();
            }
        }
    }
}
