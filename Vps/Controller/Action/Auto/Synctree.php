<?php
abstract class Vps_Controller_Action_Auto_Synctree extends Vps_Controller_Action_Auto_Abstract
{
    const ADD_LAST = 0;
    const ADD_FIRST = 1;

    protected $_primaryKey;
    protected $_table;
    protected $_tableName;
    protected $_model;
    protected $_modelName;
    protected $_searchFields = array();

    protected $_icons = array (
        'root'      => 'folder',
        'default'   => 'table',
        'edit'      => 'table_edit',
        'invisible' => 'table_key',
        'add'       => 'table_add',
        'delete'    => 'table_delete'
    );
    protected $_textField = 'name';
    protected $_parentField = 'parent_id';
    protected $_buttons = array(
        'add'       => true,
        'edit'      => false,
        'delete'    => true,
        'invisible' => null,
        'reload'    => true
    );
    protected $_rootText = 'Root';
    protected $_rootVisible = true;
    protected $_hasPosition; // Gibt es ein pos-Feld
    protected $_editDialog;
    private $_openedNodes = array();
    protected $_addPosition = self::ADD_FIRST;
    protected $_enableDD;
    protected $_defaultOrder;
    protected $_rootParentValue = null;

    public function indexAction()
    {
        $this->view->controllerUrl = $this->getRequest()->getPathInfo();
        $this->view->xtype = 'vps.autotreesync';
    }

    public function setTable($table)
    {
        $this->_model = new Vps_Model_Db(array(
            'table' => $table
        ));
    }

    public function preDispatch()
    {
        parent::preDispatch();

        if (isset($this->_tableName)) {
            $this->_table = new $this->_tableName();
        } else if (isset($this->_modelName)) {
            $this->_model = new $this->_modelName();
        }
        if (isset($this->_table)) {
            $this->_model = new Vps_Model_Db(array('table' => $this->_table));
        }
        if (!isset($this->_model)) {
            throw new Vps_Exception('$_model oder $_modelName not set');
        }

        // PrimaryKey setzen
        if (!isset($this->_primaryKey)) {
            $this->_primaryKey = $this->_model->getPrimaryKey();
            if (is_array($this->_primaryKey)) {
                $this->_primaryKey = $this->_primaryKey[1];
            }
        }

        $cols = $this->_model->getColumns();
        // Invisible-Button hinzufügen falls nicht überschrieben und in DB
        if (array_key_exists('invisible', $this->_buttons) &&
            is_null($this->_buttons['invisible']) &&
            in_array('visible', $cols))
        {
            $this->_buttons['invisible'] = true;
        }

        // Pos-Feld
        if (!isset($this->_hasPosition)) {
            $this->_hasPosition = in_array('pos', $cols);
        }
        if ($this->_hasPosition && !in_array('pos', $cols)) {
            throw new Vps_Exception("_hasPosition is true, but 'pos' does not exist in database");
        }

        foreach ($this->_icons as $k=>$i) {
            if (is_string($i)) {
                $this->_icons[$k] = new Vps_Asset($i);
            }
        }

        if (is_string($this->_defaultOrder)) {
            $this->_defaultOrder = array(
                'field' => $this->_defaultOrder,
                'direction'   => 'ASC'
            );
        }
    }

    public function jsonMetaAction()
    {
        $this->view->helpText = $this->getHelpText();
        $this->view->icons = array();
        foreach ($this->_icons as $k=>$i) {
            $this->view->icons[$k] = $i->__toString();
        }
        $this->view->search = !empty($this->_searchFields);
        $this->view->rootText = $this->_rootText;
        $this->view->rootVisible = $this->_rootVisible;
        $this->view->buttons = $this->_buttons;
        $this->view->editDialog = $this->_editDialog;
        if (is_null($this->_enableDD)) {
            $this->view->enableDD = $this->_hasPosition;
        } else {
            $this->view->enableDD = $this->_enableDD;
            if (!$this->_hasPosition) {
                $this->view->dropConfig = array('appendOnly' => true);
            }
        }
    }

    public function jsonDataAction()
    {
        $parentId = $this->_getParam('node');

        $this->_saveSessionNodeOpened($parentId, true);
        $this->_saveNodeOpened();

        if ($this->_getParam('searchValue') != '') {
            $this->view->nodes = $this->_searchNodes($this->_getParam('searchValue'));
        } else {
            $this->view->nodes = $this->_formatNodes();
        }
    }

    public function jsonNodeDataAction()
    {
        $id = $this->getRequest()->getParam('node');
        $row = $this->_model->find($id)->current();
        if ($row) {
            $this->view->data = $this->_formatNode($row);
        } else {
            throw new Vps_ClientException('Couldn\'t find row with id ' . $id);
        }
    }

    /**
     * @deprecated
     */
    protected function _getTreeWhere($parentRow = null)
    {
        return $this->_getWhere();
    }
    /**
     * @deprecated
     */
    protected function _getWhere()
    {
        return array();
    }

    protected function _formatNodes($parentRow = null)
    {
        $nodes = array();
        $rows = $this->_fetchData($parentRow);
        foreach ($rows as $row) {
            $node = $this->_formatNode($row);
            $childNodes = $this->_formatNodes($row);
            if (count($childNodes) == 0) $node['expanded'] = true;
            $node['children'] = $childNodes;

            $nodes[] = $node;
        }
        return $nodes;
    }

    protected function _searchNodes($searchValue)
    {
        $select = $this->_getSelect();
        $or = array();
        foreach ($this->_searchFields as $searchField) {
            $or[] = new Vps_Model_Select_Expr_Like($searchField, '%' . $searchValue . '%');
        }
        $select->where(new Vps_Model_Select_Expr_Or($or));
        $rows = $this->_model->getRows($select);

        $plainNodes = array();
        foreach ($rows as $row) {
            $primaryKey = $this->_primaryKey;

            $parentValue = $this->_getParentId($row);
            $pV = is_null($parentValue) ? 0 : $parentValue;
            $primaryValue = $row->$primaryKey;
            if (!isset($plainNodes[$pV][$primaryValue])) {
                $node = $this->_formatNode($row);
                $node['leaf'] = true;
                $node['allowDrag'] = false;
                $node['search'] = true;
                $plainNodes[$pV][$row->$primaryKey] = $node;
            }
            $plainNodes[$pV][$row->$primaryKey]['disabled'] = false;
            while ($parentValue) {
                $parentRow = $this->_model->getRow($parentValue);
                $parentValue = $this->_getParentId($parentRow);
                $pV = is_null($parentValue) ? 0 : $parentValue;
                $primaryValue = $parentRow->$primaryKey;
                if (!isset($plainNodes[$pV][$primaryValue])) {
                    $node = $this->_formatNode($parentRow);
                    $node['disabled'] = true;
                    $node['expanded'] = true;
                    $node['expanded'] = true;
                    $node['allowDrag'] = false;
                    $node['search'] = true;
                    $plainNodes[$pV][$primaryValue] = $node;
                }
            }
        }
        return $this->_structurePlainNodes($plainNodes, 0);
    }

    protected function _getParentId($row)
    {
        return $row->{$this->_parentField};
    }

    private function _structurePlainNodes($nodes, $parentValue)
    {
        $ret = array();
        if (!isset($nodes[$parentValue])) return array();
        foreach ($nodes[$parentValue] as $primaryValue => $node) {
            $node['children'] = $this->_structurePlainNodes($nodes, $primaryValue);
            $ret[] = $node;
        }
        return $ret;
    }

    protected function _fetchData($parentRow)
    {
        $select = $this->_getSelect();
        if ($this->_model instanceof Vps_Model_Tree_Interface) {
            if (!$parentRow) {
                return $this->_model->getRootNodes($select);
            } else {
                return $parentRow->getChildNodes($select);
            }
        } else {
            $where = $this->_getTreeWhere($parentRow);
            foreach ($where as $w) {
                $select->where($w);
            }
            if (!$parentRow) {
                if (is_null($this->_rootParentValue)) {
                    $select->whereNull($this->_parentField);
                } else {
                    $select->whereEquals($this->_parentField, $this->_rootParentValue);
                }
            } else {
                $select->whereEquals($this->_parentField, $parentRow->{$this->_primaryKey});
            }
            return $this->_model->getRows($select);
        }
    }

    protected function _getSelect()
    {
        $select = $this->_model->select();
        if ($this->_hasPosition) {
            $select->order('pos');
        } else if (!$select->hasPart('order') && $this->_defaultOrder) {
            $select->order(
                $this->_defaultOrder['field'],
                $this->_defaultOrder['direction']
            );
        }
        return $select;
    }

    protected function _formatNode($row)
    {
        $data = array();
        $primaryKey = $this->_primaryKey;
        $data['id'] = $row->$primaryKey;
        if (!$row->hasColumn($this->_textField)) {
            throw new Vps_Exception("Column '{$this->_textField}' not found, please overwrite \$_textField");
        }
        $data['text'] = $row->{$this->_textField};
        $data['data'] = $row->toArray();
        $data['leaf'] = false;
        $data['visible'] = true;
        $data['uiProvider'] = 'Vps.Tree.Node';
        if (isset($row->visible) && $row->visible == '0') { //TODO visible nicht hardcodieren
            $data['visible'] = false;
            $data['bIcon'] = $this->_icons['invisible']->__toString();
        } else {
            $data['visible'] = true;
            $data['bIcon'] = $this->_icons['default']->__toString();
        }
        $openedNodes = $this->_saveSessionNodeOpened(null, null);
        $data['uiProvider'] = 'Vps.Tree.Node';
        if ($openedNodes == 'all' ||
            isset($openedNodes[$row->$primaryKey]) ||
            isset($this->_openedNodes[$row->id])
        ) {
            $data['expanded'] = true;
        } else {
            $data['expanded'] = false;
        }
        return $data;
    }

    protected function _saveSessionNodeOpened($id, $activate)
    {
        $session = new Zend_Session_Namespace('admin');
        $key = 'treeNodes_' . get_class($this);
        $ids = is_array($session->$key) ? $session->$key : array();
        if ($id) {
            if (!$activate && isset($ids[$id])) {
                unset($ids[$id]);
            } else if ($activate) {
                $ids[$id] = true;
            }
            $session->$key = $ids;
        }
        return $ids;
    }

    protected function _saveNodeOpened()
    {
        $openedId = $this->_getParam('openedId');
        $this->_openedNodes = array();
        while ($openedId) {
            $row = $this->_model->find($openedId)->current();
            $this->_openedNodes[$openedId] = true;
            $field = $this->_parentField;
            $openedId = $row ? $row->$field : null;
        }
    }

    public function jsonVisibleAction()
    {
        $visible = $this->getRequest()->getParam('visible') == 'true';
        $id = $this->getRequest()->getParam('id');
        $row = $this->_model->find($id)->current();
        $this->_changeVisibility($row);
        $this->view->id = $row->save();
        $this->view->visible = $row->visible == '1';
        if (!isset($this->view->icon)) {
            $this->view->icon = $this->view->visible ?
                $this->_icons['invisible']->__toString() :
                $this->_icons['default']->__toString();
        }
    }

    protected function _changeVisibility(Vps_Model_Row_Interface $row)
    {
        $row->visible = $row->visible == '0' ? '1' : '0';
    }

    public function jsonAddAction()
    {
        $insert[$this->_parentField] = $this->getRequest()->getParam('parentId');
        $insert[$this->_textField] = $this->getRequest()->getParam('name');
        if ($this->_hasPosition) {
            $insert['pos'] = 0;
        }
        $row = $this->_model->createRow($insert);
        $row->save();
        $data = $this->_formatNode($row);
        foreach ($data as $k=>$i) {
            if ($i instanceof Vps_Asset) {
                $data[$k] = $i->__toString();
            }
        }
        $this->view->data = $data;
    }

    public function jsonDeleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        $row = $this->_model->find($id)->current();
        if (!$row) throw new Vps_Exception("No entry with id '$id' found");
        $this->_beforeDelete($row);
        $row->delete();
        $this->view->id = $id;
    }

    public function jsonMoveAction()
    {
        $source = $this->getRequest()->getParam('source');
        $target = $this->getRequest()->getParam('target');
        $point  = $this->getRequest()->getParam('point');

        $parentField = $this->_parentField;
        $row = $this->_model->getRow($source);
        $this->_beforeSaveMove($row);

        if ($point == 'append') {
            $targetRow = $this->_model->getRow($target);
            if (is_numeric($target) && (int)$target == 0) $target = null;

            if (!is_null($target)) {
                $targetRow = $this->_model->getRow($target);
            }
            if (is_null($target) || ($targetRow && $targetRow->$parentField != $source)) {
                $row->$parentField = $target;
                if ($this->_hasPosition) {
                    $row->pos = '1';
                }
            } else {
                $this->view->error = trlVps('Cannot move here. View has been reloaded, please try again.');
            }
        } else {
            $targetRow = $this->_model->getRow($target);
            if ($targetRow) {
                if ($this->_hasPosition) {
                    $targetPosition = $targetRow->pos;
                    if ($point == 'below') {
                        $targetPosition++;
                    }
                    if ($row->$parentField == $targetRow->$parentField &&
                        $row->pos < $targetRow->pos
                    ) {
                         $targetPosition--;
                    }
                    $row->pos = $targetPosition;
                }
                $row->$parentField = $targetRow->$parentField;
            } else {
                $this->view->error = trlVps('Cannot move here.');
            }
        }
        $row->save();

        $row = $this->_model->find($row->id)->current();
        $primaryKey = $this->_model->getPrimaryKey();
        $before = null;
        $select = $this->_getSelect($this->_getTreeWhere());
        $parentValue = $row->$parentField;
        if (!$parentValue) {
            if (is_null($this->_rootParentValue)) {
                $select->whereNull($this->_parentField);
            } else {
                $select->whereEquals($this->_parentField, $this->_rootParentValue);
                $parentValue = $this->_rootParentValue;
            }
        } else {
            $select->whereEquals($this->_parentField, $parentValue);
        }
        foreach ($this->_model->fetchAll($select) as $r) {
            if ($before === true) $before = $r->$primaryKey;
            if ($r->$primaryKey == $source) {
                $before = true;
            }
        }
        if ($before === true) $before = null;

        $this->view->parent = $parentValue;
        $this->view->node = $source;
        $this->view->before = $before;
    }

    protected function _beforeSaveMove($row) {}
    protected function _beforeDelete(Vps_Model_Row_Interface $row) {}

    public function jsonCollapseAction()
    {
        $id = $this->getRequest()->getParam('id');
        $this->_saveSessionNodeOpened($id, false);
    }

    public function jsonExpandAction()
    {
        $id = $this->getRequest()->getParam('id');
        $this->_saveSessionNodeOpened($id, true);
    }
}
