<?php
class Kwf_Model_RowsSubModel_MirrorCacheSimple extends Kwf_Model_RowsSubModel_Proxy
{
    protected $_rowClass = 'Kwf_Model_RowsSubModel_MirrorCacheSimple_Row';

    protected $_sourceModel;

    public function __construct(array $config = array())
    {
        if (isset($config['sourceModel'])) $this->_sourceModel = $config['sourceModel'];
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
        if (is_string($this->_sourceModel)) {
            $this->_sourceModel = Kwf_Model_Abstract::getInstance($this->_sourceModel);
        }
        $this->_sourceModel->addProxyContainerModel($this);

        if (!($this->getProxyModel() instanceof Kwf_Model_RowsSubModel_Interface)) {
            throw  new Kwf_Exception("Proxy model doesn't implement Kwf_Model_RowsSubModel_Interface");
        }
    }
    //kann gesetzt werden von proxy (rekursiv bei proxys)
    public function addProxyContainerModel($m)
    {
        parent::addProxyContainerModel($m);
        $this->_sourceModel->addProxyContainerModel($m);
    }

    public function getSourceModel()
    {
        return $this->_sourceModel;
    }

    public function createRow(array $data=array())
    {
        throw new Kwf_Exception('getRows is not possible for Kwf_Model_RowsSubModel_MirrorCacheSimple');
    }

    public function getRows($where=null, $order=null, $limit=null, $start=null)
    {
        throw new Kwf_Exception('getRows is not possible for Kwf_Model_RowsSubModel_MirrorCacheSimple');
    }

    public function clearRows()
    {
        parent::clearRows();
        $this->_sourceModel->clearRows();
    }
}
