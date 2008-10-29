<?php
class Vps_Model_Proxy extends Vps_Model_Abstract
{
    protected $_proxyModel;
    protected $_rowClass = 'Vps_Model_Proxy_Row';
    protected $_rowsetClass = 'Vps_Model_Proxy_Rowset';

    public function __construct(array $config = array())
    {
        if (isset($config['proxyModel'])) $this->_proxyModel = $config['proxyModel'];
        parent::__construct($config);
    }

    public function getProxyModel()
    {
        return $this->_proxyModel;
    }

    public function createRow(array $data=array())
    {
        $proxyRow = $this->_proxyModel->createRow($data);
        $ret = new $this->_rowClass(array(
            'row' => $proxyRow,
            'model' => $this
        ));
        $this->_rows[$proxyRow->getInternalId()] = $ret;
        return $ret;
    }

    public function getRowByProxiedRow($proxiedRow)
    {
        $id = $proxiedRow->getInternalId();
        if (!isset($this->_rows[$id])) {
            $this->_rows[$id] = new $this->_rowClass(array(
                'row' => $proxiedRow,
                'model' => $this
            ));
        }
        return $this->_rows[$id];
    }

    public function getPrimaryKey()
    {
        return $this->_proxyModel->getPrimaryKey();
    }

    public function isEqual(Vps_Model_Interface $other)
    {
        if (get_class($other) == get_class($this)
            && $this->_proxyModel->isEqual($other->_proxyModel)
        ) {
            return true;
        }
        return false;
    }

    public function getColumns()
    {
        return $this->_proxyModel->getColumns();
    }

    public function hasColumn($col)
    {
        if ($this->_proxyModel->hasColumn($col)) return true;
        foreach ($this->getSiblingModels() as $m) {
            if ($m->hasColumn($col)) return true;
        }
        return false;
    }

    public function getRows($where=null, $order=null, $limit=null, $start=null)
    {
        $proxyRowset = $this->_proxyModel->getRows($where, $order, $limit, $start);
        return new $this->_rowsetClass(array(
            'rowset' => $proxyRowset,
            'rowClass' => $this->_rowClass,
            'model' => $this
        ));
    }

    public function countRows($where = array())
    {
        return $this->_proxyModel->countRows($where);
    }

    public function getUniqueIdentifier() {
        if (isset($this->_proxyModel)) {
            return $this->_proxyModel->getUniqueIdentifier().'_proxy';
        } else {
            throw new Vps_Exception("no unique identifier set");
        }
    }
}
