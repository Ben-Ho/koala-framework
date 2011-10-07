<?php
abstract class Kwf_Controller_Action_Auto_Tree extends Kwf_Controller_Action_Auto_Synctree
{
    public function indexAction()
    {
        parent::indexAction();
        $this->view->xtype = 'kwf.autotree';
    }

    protected function _formatNodes($parentId = null)
    {
        if (!$parentId) $parentId = $this->_getParam('node');
        if ($parentId) {
            $parentRow = $this->_model->getRow($parentId);
        } else {
            $parentRow = null;
        }
        $rows = $this->_fetchData($parentRow);
        $nodes = array();
        foreach ($rows as $row) {
            $data = $this->_formatNode($row);
            if ($data) {
                foreach ($data as $k=>$i) {
                    if ($i instanceof Kwf_Asset) {
                        $data[$k] = $i->__toString();
                    }
                }
                $nodes[]= $data;
            }
        }
        return $nodes;
    }

    protected function _formatNode($row)
    {
        $data = parent::_formatNode($row);
        unset($data['children']);

        $select = $this->_model->select($this->_getTreeWhere($row));
        $select->whereEquals($this->_parentField, $row->{$this->_primaryKey});
        if ($this->_model->fetchCount($select)) {
            $id = $row->{$this->_primaryKey};
            $openedNodes = $this->_saveSessionNodeOpened(null, null);
            if ($openedNodes == 'all' ||
                (isset($openedNodes[$id]) && $openedNodes[$id]) ||
                isset($this->_openedNodes[$id]) ||
                $this->_getParam('openedId') == $id
            ) {
                $data['expanded'] = true;
            } else {
                $data['expanded'] = false;
            }
        } else {
            $data['children'] = array();
            $data['expanded'] = true;
        }
        return $data;
    }
}
