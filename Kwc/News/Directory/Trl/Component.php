<?php
class Kwc_News_Directory_Trl_Component extends Kwc_Directories_Item_Directory_Trl_Component
{
    public static function getSettings($masterComponentClass)
    {
        $ret = parent::getSettings($masterComponentClass);
        $ret['childModel'] = 'Kwc_News_Directory_Trl_Model';

        $ret['flags']['hasResources'] = true;
        return $ret;
    }

    public function getSelect()
    {
        $select = parent::getSelect();
        $select->where('publish_date <= NOW()');
        if (Kwc_Abstract::getSetting($this->_getSetting('masterComponentClass'), 'enableExpireDate')) {
            $select->where('expiry_date >= NOW() OR ISNULL(expiry_date)');
        }
        $select->order('publish_date', 'DESC');
        return $select;
    }
}
