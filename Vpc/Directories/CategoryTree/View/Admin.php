<?php
class Vpc_Directories_CategoryTree_View_Admin
    extends Vpc_Admin
{
    public function onRowUpdate($row)
    {
        $this->_removeCache($row);
        parent::onRowUpdate($row);
    }

    public function onRowDelete($row)
    {
        $this->_removeCache($row);
        parent::onRowDelete($row);
    }

    public function onRowInsert($row)
    {
        $this->_removeCache($row);
        parent::onRowInsert($row);
    }

    private function _removeCache($row)
    {
        if ($row instanceof Vps_Db_Table_Row && $row->getTable() instanceof Vpc_Directories_CategoryTree_Directory_ItemsToCategoriesModel) {
            $info = Vpc_Directories_Category_Detail_List_Component::getTableReferenceData(
                get_class($row->getTable()), $schema = 'Category'
            );
            $table = new $info['refTableName']();

            $parentRow = $table->find($row->category_id)->current();

            do {
                $cacheId = Vpc_Directories_CategoryTree_View_Component::getItemCountCacheId($parentRow);
                Vpc_Directories_CategoryTree_View_Component::getItemCountCache()->remove($cacheId);

                $parentRow = $parentRow->findParentRow($info['refTableName']);
            } while ($parentRow);
        } else if ($row instanceof Vps_Db_Table_Row && $row->getTable() instanceof Vpc_Directories_CategoryTree_Directory_Model) {
            $parentRow = $row;
            do {
                $cacheId = Vpc_Directories_CategoryTree_View_Component::getItemCountCacheId($parentRow);
                Vpc_Directories_CategoryTree_View_Component::getItemCountCache()->remove($cacheId);

                $parentRow = $parentRow->findParentRow($row->getTable()->info(Zend_Db_Table_Abstract::NAME));
            } while ($parentRow);
        } else {
            // Todo: wenn item in der admin bearbeitet wird (zB visible auf 0),
            // dann müsste man es neu berechnen. wird atm durch die cacheLifetime kompensiert
        }
    }
}
