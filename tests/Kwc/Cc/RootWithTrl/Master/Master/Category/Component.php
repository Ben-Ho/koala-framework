<?php
class Kwc_Cc_RootWithTrl_Master_Master_Category_Component extends Kwc_Root_Category_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['page']['model'] = 'Kwc_Cc_RootWithTrl_Master_Master_Category_PagesModel';
        $ret['generators']['page']['component'] = array(
            'empty' => 'Kwc_Basic_Empty_Component'
        );
        return $ret;
    }
}