<?php
class Vpc_Root_TrlRoot_Chained_Component extends Vpc_Abstract
{
    public static function getSettings($masterComponentClass)
    {
        $ret = parent::getSettings();
        $copySettings = array('editComponents');
        $ret = Vpc_Chained_Abstract_Component::getChainedSettings($ret, $masterComponentClass, 'Trl', $copySettings, array());
        $ret['flags']['showInPageTreeAdmin'] = true;
        $ret['flags']['hasHome'] = true;
        $ret['flags']['hasLanguage'] = true;
        $ret['flags']['chainedType'] = 'Trl';
        return $ret;
    }

    public function getLanguage()
    {
        return $this->getData()->language;
    }
}
