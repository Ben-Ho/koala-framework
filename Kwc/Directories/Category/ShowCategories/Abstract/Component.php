<?php
abstract class Kwc_Directories_Category_ShowCategories_Abstract_Component extends Kwc_Directories_List_Component
{
    abstract public function getCategoryIds();

    public function getSelect()
    {
        $select = parent::getSelect();
        if (!$select) return null;

        $s = new Kwf_Component_Select();
        $s->whereGenerator('categories');
        $tableName = Kwc_Abstract::getSetting(
            $this->getItemDirectory()->getChildComponent($s)->componentClass,
            'categoryToItemModelName'
        );
        $refData = Kwc_Directories_Category_Detail_List_Component::getTableReferenceData($tableName, 'Item');

        $select->join($refData['tableName'],
                      $refData['tableName'].'.'.$refData['itemColumn'].'='
                        .$refData['refTableName'].'.'.$refData['refItemColumn'],
                      array());
        $ids = $this->getCategoryIds();
        if (!$ids) return null;
        $select->where($refData['tableName'].'.category_id IN ('.implode(',', $ids).')');
        return $select;
    }
}
