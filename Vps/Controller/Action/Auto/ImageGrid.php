<?php
abstract class Vps_Controller_Action_Auto_ImageGrid extends Vps_Controller_Action_Auto_Abstract
{
    // srcField muss überschrieben werden (bildpfad), labelField ist standard __toString()
    protected $_srcField;
    protected $_labelField;

    protected $_model;

    protected $_primaryKey;
    protected $_paging = 0;

    protected $_textField = 'name';
    protected $_buttons = array(
        'add'       => true,
        'edit'      => false,
        'delete'    => true,
        'invisible' => null,
        'reload'    => true
    );
    protected $_editDialog;
    protected $_defaultOrder;

    public function indexAction()
    {
        $this->view->controllerUrl = $this->getRequest()->getPathInfo();
        $this->view->xtype = 'vps.imagegrid';
    }

    public function preDispatch()
    {
        parent::preDispatch();

        if (is_string($this->_model)) {
            $this->_model = new $this->_model();
        }

        // PrimaryKey setzen
        if (!isset($this->_primaryKey)) {
            $this->_primaryKey = $this->_model->getPrimaryKey();
        }

        if (is_string($this->_defaultOrder)) {
            $this->_defaultOrder = array(
                'field' => $this->_defaultOrder,
                'direction'   => 'ASC'
            );
        }
    }

    public function jsonDataAction()
    {
        $limit = null; $start = null; $order = 0;
        if ($this->_paging) {
            $limit = $this->getRequest()->getParam('limit');
            $start = $this->getRequest()->getParam('start');
            if (!$limit) {
                if (!is_array($this->_paging) && $this->_paging > 0) {
                    $limit = $this->_paging;
                } else if (is_array($this->_paging) && isset($this->_paging['pageSize'])) {
                    $limit = $this->_paging['pageSize'];
                } else {
                    $limit = $this->_paging;
                }
            }
        }
        $order = $this->_defaultOrder;
        $primaryKey = $this->_primaryKey;

        $rowSet = $this->_fetchData($order, $limit, $start);
        if (!is_null($rowSet)) {
            $rows = array();
            foreach ($rowSet as $row) {
                $r = array();
                if (is_array($row)) {
                    $row = (object)$row;
                }
                if (!$this->_hasPermissions($row, 'load')) {
                    throw new Vps_Exception("You don't have the permissions to load this row");
                }
                if (!isset($r[$primaryKey]) && isset($row->$primaryKey)) {
                    $r[$primaryKey] = $row->$primaryKey;
                }

                if (!$this->_labelField && $row instanceof Vps_Model_Row_Interface) {
                    $r['label'] = $row->__toString();
                } else if ($this->_labelField) {
                    $r['label'] = $row->{$this->_labelField};
                } else {
                    throw new Vps_Exception("You have to set _labelField in the ImageGrid Controller");
                }

                if ($this->_srcField) {
                    $r['src'] = $row->{$this->_srcField};
                } else {
                    throw new Vps_Exception("You have to set _srcField in the ImageGrid Controller");
                }

                $rows[] = $r;
            }

            $this->view->rows = $rows;
            if ($this->_paging) {
                $this->view->total = $this->_fetchCount();
            } else {
                $this->view->total = sizeof($rows);
            }
        } else {
            $this->view->total = 0;
            $this->view->rows = array();
        }

        if ($this->getRequest()->getParam('meta')) {
            $this->_appendMetaData();
        }
    }

    protected function _appendMetaData()
    {
        $this->view->metaData = array();

        $this->view->metaData['root'] = 'rows';
        $this->view->metaData['id'] = $this->_primaryKey;
        $this->view->metaData['fields'] = array('id', 'label', 'src');
        $this->view->metaData['totalProperty'] = 'total';
        $this->view->metaData['successProperty'] = 'success';
        $this->view->metaData['buttons'] = (object)$this->_buttons;
        $this->view->metaData['permissions'] = (object)$this->_permissions;
        $this->view->metaData['paging'] = $this->_paging;
        $this->view->metaData['editDialog'] = $this->_editDialog;
    }

    protected function _hasPermissions($row, $action)
    {
        return true;
    }

    protected function _getSelect()
    {
        $ret = $this->_model->select();
        return $ret;
    }

    protected function _fetchData($order, $limit, $start)
    {
        if (!isset($this->_model)) {
            throw new Vps_Exception("Either _model has to be set or _fetchData has to be overwritten.");
        }

        $select = $this->_getSelect();
        if (is_null($select)) return null; //wenn _getSelect null zurückliefert nichts laden
        $select->limit($limit, $start);
        if ($order) $select->order($order);
        return $this->_model->getRows($select);
    }

    protected function _fetchCount()
    {
        if (!isset($this->_model)) {
            return count($this->_fetchData(null, 0, 0));
        }

        $select = $this->_getSelect();
        if (is_null($select)) return 0;
        return $this->_model->countRows($select);
    }

    public function jsonDeleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        $row = $this->_model->getRow($id);
        if (!$row) throw new Vps_Exception("No entry with id '$id' found");
        $this->_beforeDelete($row);
        $row->delete();
        $this->view->id = $id;
        $this->_afterDelete();
    }

    protected function _beforeDelete(Vps_Model_Row_Interface $row)
    {
    }

    protected function _afterDelete()
    {
    }
}
