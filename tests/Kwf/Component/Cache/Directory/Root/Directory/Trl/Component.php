<?php
class Kwf_Component_Cache_Directory_Root_Directory_Trl_Component extends Kwc_Directories_Item_Directory_Trl_Component
{
    public static function getSettings($masterComponentClass)
    {
        $ret = parent::getSettings($masterComponentClass);
        $ret['childModel'] = 'Kwf_Component_Cache_Directory_Root_Directory_Trl_Model';
        $ret['flags']['chainedType'] = 'Trl';
        return $ret;
    }
}
