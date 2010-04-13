<?php
class Vpc_Basic_LinkTag_Intern_Data extends Vps_Component_Data
{
    private $_data;

    protected function _getRow()
    {
        $m = Vpc_Abstract::createModel($this->componentClass);
        return $m->getRow($this->dbId);
    }

    protected function _getData()
    {
        if ($row = $this->_getRow()) {
            $ret = Vps_Component_Data_Root::getInstance()->getComponentByDbId(
                $row->target,
                array('subroot' => $this)
            );
            return $ret;
        }
        return false;
    }

    public final function getLinkedData()
    {
        if (!isset($this->_data)) {
            $this->_data = $this->_getData();
            if (!$this->_data) $this->_data = false;
        }
        return $this->_data;
    }

    public function __get($var)
    {
        if ($var == 'url') {
            if (!$this->getLinkedData()) return '';
            return $this->getLinkedData()->url;
        } else if ($var == 'rel') {
            if (!$this->getLinkedData()) return '';
            return $this->getLinkedData()->rel;
        } else {
            return parent::__get($var);
        }
    }

}
