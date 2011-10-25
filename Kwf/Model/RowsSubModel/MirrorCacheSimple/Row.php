<?php
class Kwf_Model_RowsSubModel_MirrorCacheSimple_Row extends Kwf_Model_RowsSubModel_Proxy_Row
{
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $sourceModel = $this->getModel()->getSourceModel();
        $pk = $sourceModel->getPrimaryKey();
        $sourceRow = $sourceModel->getRow($this->$pk);
        if (!$sourceRow) $sourceRow = $sourceModel->createRow();
        foreach ($this->getProxiedRow()->toArray() as $k=>$i) {
            if ($sourceModel->hasColumn($k)) {
                $sourceRow->$k = $i;
            }
        }
        $sourceRow->save();

        //daten von sourceRow übernehmen wie zB auto_increment
        foreach ($sourceRow->toArray() as $k=>$i) {
            $this->getProxiedRow()->$k = $i;
        }
    }

    public function _beforeDelete()
    {
        parent::_beforeDelete();
        $pk = $this->getModel()->getPrimaryKey();
        $sourceRow = $this->getModel()->getSourceModel()->getRow($this->$pk);
        $sourceRow->delete();
    }
}
