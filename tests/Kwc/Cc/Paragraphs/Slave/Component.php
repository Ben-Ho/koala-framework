<?php
class Kwc_Cc_Paragraphs_Slave_Component extends Kwc_Chained_Start_Component
{
    public static function getSettings($masterComponentClass)
    {
        $ret = parent::getSettings($masterComponentClass);
        $ret = Kwc_Chained_Abstract_Component::getChainedSettings($ret, $masterComponentClass, 'Cc');
        $ret['flags']['chainedType'] = 'Cc';
        return $ret;
    }
}
