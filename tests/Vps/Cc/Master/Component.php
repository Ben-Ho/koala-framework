<?php
class Vps_Cc_Master_Component extends Vpc_Abstract
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['master'] = array(
            'class' => 'Vpc_Chained_Trl_MasterGenerator',
            'component' => 'Vps_Cc_Master_Master_Component',
        );
        $ret['generators']['chained'] = array(
            'class' => 'Vpc_Chained_Abstract_ChainedGenerator',
            'component' => 'Vpc_Root_TrlRoot_Chained_Component.Vps_Cc_Master_Master_Component',
            'filenameColumn' => 'filename',
            'nameColumn' => 'name',
            'uniqueFilename' => true,
        );
        $ret['childModel'] = new Vpc_Root_TrlRoot_Model(array(
            'master' => 'master',
            'slave' => 'slave'
        ));
        $ret['chainedType'] = 'Cc';
        return $ret;
    }
}