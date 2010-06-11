<?php
class Vps_Component_ModelObserver
{
    static private $_instance;
    private $_process = array(
        'insert' => array(),
        'update' => array(),
        'delete' => array(),
        'save'   => array()
    );
    private $_skipFnF = true;
    private $_processed = array();
    private $_disableCache = false; // für Tests

    public static function getInstance()
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    //für tests
    public function setSkipFnF($v)
    {
        $this->_skipFnF = $v;
    }

    //für tests
    public function setDisableCache($disable)
    {
        $this->_disableCache = $disable;
    }

    public function clear()
    {
        foreach (array_keys($this->_process) as $i) {
            $this->_process[$i] = array();
        }
        $this->_processed = array();
    }


    public function insert($source)
    {
        $this->_process['insert'][] = $source;
    }

    public function update($source)
    {
        $this->_process['update'][] = $source;
    }

    public function save($source)
    {
        $this->_process['save'][] = $source;
    }

    public function delete($source)
    {
        // Wird hier direkt aufgerufen, weil wenn später aufgerufen, ist row schon gelöscht
        if (!Vps_Component_Data_Root::getComponentClass()) return;
        $this->_processCache($source);
    }

    protected function _processCache($source)
    {
        if ($source instanceof Vps_Model_Interface) {
            $model = $source;
            $id = null;
        } else {
            $row = $source;
            if ($row instanceof Zend_Db_Table_Row_Abstract) {
                $model = $row->getTable();
                $primary = current($model->info('primary'));
            } else {
                $model = $row->getModel();
                $primary = $model->getPrimaryKey();
                if (get_class($model) == 'Vps_Model_Db') $model = $model->getTable();
                if ($model instanceof Vps_Component_Cache_MetaModel ||
                    $model instanceof Vps_Component_Cache_Model ||
                    ($model instanceof  Vps_Model_Field && !$primary)
                ) {
                    return array();
                }
            }
            $id = is_array($primary) ? null : $row->$primary;
            $componentId = isset($row->component_id) ? $row->component_id : null;
        }
        if (get_class($model) == 'Vps_Db_Table') return array();
        if ($this->_skipFnF) {
            $m = $model;
            while ($m instanceof Vps_Model_Proxy) { $m = $m->getProxyModel(); }
            if ($m instanceof Vps_Model_FnF) return array();
        }
        $modelname = get_class($model);
        if (!isset($this->_processed[$modelname]) || !in_array($id, $this->_processed[$modelname])) {
            if (!isset($this->_processed[$modelname])) $this->_processed[$modelname] = array();
            $this->_processed[$modelname][] = $id;
            if (!$this->_disableCache) {
                Vps_Component_Cache::getInstance()->clean(
                    Vps_Component_Cache::CLEANING_MODE_META, $source
                );
            }
            return array($modelname => $id);
        }
        return array();
    }

    public function process()
    {
        $ret = array();

        // View Cache
        if (!Vps_Component_Data_Root::getComponentClass()) return $ret;
        foreach ($this->_process as $action => $process) {
            foreach (array_reverse($process) as $source) {
                foreach ($this->_processCache($source) as $modelname => $id) {
                    if (!isset($ret[$modelname])) $ret[$modelname] = array();
                    $ret[$modelname][] = $id;
                }
            }
        }
        $this->clear();

        // Suchindex
        if (class_exists('Vps_Dao_Index', false)) { //Nur wenn klasse jemals geladen wurde kann auch was zu processen drin sein
            Vps_Dao_Index::process();
        }

        return $ret;
    }
}
