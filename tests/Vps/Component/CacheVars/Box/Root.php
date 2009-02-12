<?php
class Vps_Component_CacheVars_Box_Root extends Vps_Component_NoCategoriesRoot
{
    public static function getSettings()
    {
        $ret = parent::getSettings();

        $ret['generators']['boxNotOverwritten'] = array(
            'component' => 'Vpc_Basic_Empty_Component',
            'class' => 'Vps_Component_Generator_Page_Static'
        );

        $ret['generators']['boxOverwritten'] = array(
            'component' => 'Vpc_Basic_Empty_Component',
            'class' => 'Vps_Component_Generator_Page_Static'
        );

        $ret['generators']['box'] = array(
            'component' => 'Vps_Component_CacheVars_Box_Box',
            'class' => 'Vps_Component_Generator_Box_Static',
            'inherit' => true
        );
        $ret['generators']['boxUnique'] = array(
            'component' => 'Vps_Component_CacheVars_Box_Box',
            'class' => 'Vps_Component_Generator_Box_Static',
            'inherit' => true,
            'unique' => true,
        );

        unset($ret['generators']['page']);
        return $ret;
    }
}
