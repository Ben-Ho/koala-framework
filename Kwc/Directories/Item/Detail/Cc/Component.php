<?php
class Kwc_Directories_Item_Detail_Cc_Component extends Kwc_Abstract_Composite_Cc_Component
{
    public static function getSettings($masterComponentClass)
    {
        $ret = parent::getSettings($masterComponentClass);
        $ret['hasModifyItemData'] = true;
        return $ret;
    }

    public static function modifyItemData(Kwf_Component_Data $item)
    {
    }
}
