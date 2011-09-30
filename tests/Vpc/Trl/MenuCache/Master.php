<?php
class Vpc_Trl_MenuCache_Master extends Vpc_Root_TrlRoot_Master_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators'] = array();
        $ret['generators']['category'] = array(
            'class' => 'Vpc_Root_CategoryGenerator',
            'component' => 'Vpc_Trl_MenuCache_Category_Component',
            'model' => new Vps_Model_FnF(array(
                'data' => array(
                    array('id' => 'main', 'name' => 'main'),
                    array('id' => 'bottom', 'name' => 'bottom'),
                )
            ))
        );
        return $ret;
    }
}
