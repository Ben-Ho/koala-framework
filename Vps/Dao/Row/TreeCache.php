<?php
class Vps_Dao_Row_TreeCache extends Vps_Db_Table_Row_Abstract
{
    private $_component;
    
    public function findParentComponent()
    {
        return $this->findParentRow('Vps_Dao_TreeCache', 'Parent');
    }

    public function getComponent()
    {
        if (!isset($this->_component)) {
            $component = new $this->component_class($this);
            $this->_component = $component;
        }
        return $this->_component;
    }

    public function getTitle()
    {
        $title = array();
        $row = $this;
        do {
            $title[] = $row->name;
        } while ($row = $row->findParentPage());
        return implode(' - ', $title);
    }

    public function findParentPage()
    {
        if (!$this->parent_url) return null;
        return $this->getTable()->findPageByPath($this->parent_url);
    }

    //sucht die Page in der die Komponente liegt, kann sich selbst sein
    public function findPage()
    {
        if ($this->url) return $this;
        return $this->getTable()->findPageByPath($this->tree_url);
    }

    public function findChildComponents()
    {
        $where = array('parent_component_id = ?' => $this->component_id);
        return $this->getTable()->fetchAll($where, 'pos');
    }

    //sucht eine unter-komponente der aktuellen komponente, nur suffix wird benötigt
    public function findChildComponent($suffix)
    {
        $where = array('component_id = ?' => $this->component_id.$suffix);
        return $this->getTable()->fetchAll($where);
    }

    public function findChildPages()
    {
        $where = array('parent_component_id = ?' => $this->component_id);
        $where[] = 'NOT ISNULL(url)';
        return $this->getTable()->fetchAll($where, 'pos');
    }

    public function findMenuChildPages()
    {
        $where = array('parent_component_id = ?' => $this->component_id);
        $where[] = 'NOT ISNULL(url)';
        $where[] = 'menu = 1';
        return $this->getTable()->fetchAll($where, 'pos');
    }

    protected function _postUpdate()
    {
        Zend_Db_Table_Row_Abstract::_postUpdate();
    }
    protected function _postInsert()
    {
        Zend_Db_Table_Row_Abstract::_postInsert();
    }
    protected function _postDelete()
    {
        Zend_Db_Table_Row_Abstract::_postDelete();
    }
}
