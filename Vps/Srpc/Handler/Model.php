<?php
class Vps_Srpc_Handler_Model
{
    protected $_model;

    public function __construct(array $config = array())
    {
        if (isset($config['model']) && is_object($config['model']) && $config['model'] instanceof Vps_Model_Interface) {
            $this->_model = $config['model'];
        }
        $this->_init();
    }

    protected function _init()
    {
    }

    public function getModel()
    {
        if (!$this->_model) {
            throw new Vps_Srpc_Exception("'model' has not been set for '".get_class($this)."'. Either set it in _init() or use the config option 'model'.");
        }
        return $this->_model;
    }

    public function getRow($id)
    {
        $row = $this->getModel()->getRow($id);
        if (!$row) return null;
        return $row->toArray();
    }

    public function countRows($select = array())
    {
        return $this->getModel()->countRows($select);
    }

    public function getRows($where=null, $order=null, $limit=null, $start=null)
    {
        $result = $this->getModel()->getRows($where, $order, $limit, $start);
        if (!$result || !$result->current()) return null;
        return $result->toArray();
    }

    public function getColumns()
    {
        return $this->getModel()->getColumns();
    }

    public function getPrimaryKey()
    {
        return $this->getModel()->getPrimaryKey();
    }

    public function rowSave($id, $data)
    {
        if (!$data || !is_array($data)) return false;

        if (is_null($id)) {
            $row = $this->getModel()->createRow();
        } else {
            $row = $this->getModel()->getRow($id);
        }

        if (!$row) return false;

        foreach ($data as $col => $value) {
            $row->$col = $value;
        }
        $row->save();

        return $row->toArray();
    }

    public function rowDelete($id)
    {
        $row = $this->getModel()->getRow($id);
        if (!$row) return false;
        $row->delete();
        return true;
    }
}
