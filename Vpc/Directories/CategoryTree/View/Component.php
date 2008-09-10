<?php
class Vpc_Directories_CategoryTree_View_Component
    extends Vpc_Directories_List_ViewPage_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['placeholder']['linkPrefix'] = '';
        return $ret;
    }

    public static function getItemCountCacheId($row)
    {
        // Row kann von hier (Model) oder von Admin (DB-Row) kommen
        if ($row instanceof Vps_Model_Row_Interface) $row = $row->getRow();
        return 'VpcDirectoriesCategoryTreeViewComponent_category'.get_class($row->getTable()).$row->id.'_itemCount';
    }

    public static function getItemCountCache()
    {
        $frontendOptions = array('lifetime' => 3600);
        $backendOptions = array('cache_dir' => 'application/cache/component/');
        return Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
    }

    protected function _getItems()
    {
        $items = parent::_getItems();

        $cache = self::getItemCountCache();

        foreach ($items as &$item) {
            $cacheId = self::getItemCountCacheId($item->row);

            if (($item->listCount = $cache->load($cacheId)) == false) {
                if (!isset($itemDirectory)) {
                    $itemDirectory = $this->getData()->parent->getComponent()->getItemDirectory();
                }
                if (!isset($connectData)) {
                    $tableName = Vpc_Abstract::getSetting(
                        $itemDirectory->componentClass, 'categoryToItemTableName'
                    );
                    $connectData = Vpc_Directories_Category_Detail_List_Component::getTableReferenceData(
                        $tableName
                    );
                }
                if (!isset($directoryComponent)) {
                    $directoryComponent = $this->getData()->parent->getComponent()
                        ->getItemDirectory()->parent->getComponent();
                }

                $categoryIds = $item->row->getRow()->getRecursiveChildCategoryIds(array(
                    'visible = 1'
                ));

                $select = $directoryComponent->getSelect();
                $select->join(
                    $connectData['tableName'],
                    "$connectData[refTableName].$connectData[refItemColumn] = $connectData[tableName].$connectData[itemColumn]",
                    array()
                );
                $select->where("$connectData[tableName].category_id IN(".implode(',', $categoryIds).")");

                $item->listCount = $directoryComponent->getData()->countChildComponents($select);

                $cache->save($item->listCount, $cacheId);
            }
        }

        return $items;
    }
}

