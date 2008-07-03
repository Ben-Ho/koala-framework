<?php
class Vpc_Abstract_Admin extends Vps_Component_Abstract_Admin
{
    protected function _getRow($componentId)
    {
        if (!Vpc_Abstract::hasSetting($this->_class, 'tablename')) return null;
        $tablename = Vpc_Abstract::getSetting($this->_class, 'tablename');
        if ($tablename) {
            $table = new $tablename(array('componentClass'=>$this->_class));
            return $table->find($componentId)->current();
        }
        return null;
    }

    protected function _getRows($componentId)
    {
        $tablename = Vpc_Abstract::getSetting($this->_class, 'tablename');
        if ($tablename) {
            $table = new $tablename(array('componentClass' => $this->_class));
            $where = array(
                'component_id = ?' => $componentId
            );
            return $table->fetchAll($where);
        }
        return array();
    }

    public function delete($componentId)
    {
        $row = $this->_getRow($componentId);
        if ($row) {
            $row->delete();
        }
    }

    public function duplicate($component)
    {
    }

    function createFormTable($tablename, $fields)
    {
        if (!$this->_tableExists($tablename)) {
            $f = array();
            $f['component_id'] = 'varchar(255) NOT NULL';
            $f = array_merge($f, $fields);

            $sql = "CREATE TABLE `$tablename` (";
            foreach ($f as $field => $data) {
                $sql .= " `$field` $data," ;
            }
            $sql .= 'PRIMARY KEY (component_id))';
            $sql .= 'ENGINE=InnoDB DEFAULT CHARSET=utf8';
            $this->_db->query($sql);

            if (isset($fields['vps_upload_id'])) {
                $this->_db->query("ALTER TABLE $tablename
                    ADD INDEX (vps_upload_id)");
                $this->_db->query("ALTER TABLE $tablename
                    ADD FOREIGN KEY (vps_upload_id)
                    REFERENCES vps_uploads (id)
                    ON DELETE RESTRICT ON UPDATE RESTRICT");
            }
            return true;
        }
        return false;
    }

    protected function _tableExists($tablename)
    {
        return in_array($tablename, $this->_db->listTables());
    }
    
    public function clearCache($caller)
    {
        // Cache der aktuellen Komponente löschen
        if ($caller instanceof Vpc_Row &&
            Vpc_Abstract::getSetting($this->_class, 'tablename') == $caller->getTableClass()
        ) {
            $components = Vps_Component_Data_Root::getInstance()->getComponentsByDbId($caller->component_id);
            foreach ($components as $c) {
                Vps_Component_Cache::getInstance()->remove($c->componentId);
            }
        }
    }
}
