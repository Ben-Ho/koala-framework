<?php
class Vps_Component_Generator_Indirect_Root extends Vpc_Root_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        unset($ret['generators']['page']);
        unset($ret['generators']['box']);
        $ret['generators']['static'] = array(
            'class' => 'Vps_Component_Generator_Static',
            'component' => 'Vps_Component_Generator_Indirect_Flag'
        );
        return $ret;
    }
}
?>