<?php
class Kwc_List_Gallery_Trl_Component extends Kwc_Abstract_List_Trl_Component
{
    public static function getSettings($masterComponent)
    {
        $ret = parent::getSettings($masterComponent);
        $ret['ownModel'] = 'Kwf_Component_FieldModel';
        return $ret;
    }
}
