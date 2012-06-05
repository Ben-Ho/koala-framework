<?php
class Kwc_Cc_PageTree_Master_Category_Component extends Kwc_Root_Category_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['page']['model'] = 'Kwc_Cc_PageTree_Master_Category_PagesModel';
        $ret['generators']['page']['component'] = array(
            'none' => 'Kwc_Basic_None_Component',
            'test' => 'Kwc_Cc_PageTree_Master_Category_Test_Component',
        );
        return $ret;
    }
}
