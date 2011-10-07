<?php
class Kwc_Cc_Paragraphs_Master_Paragraphs_Component extends Kwc_Paragraphs_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['childModel'] = 'Kwc_Cc_Paragraphs_Master_Paragraphs_TestModel';
        $ret['generators']['paragraphs']['component'] = array(
            'simple' => 'Kwc_Cc_Paragraphs_Master_Paragraphs_Simple_Component'
        );
        return $ret;
    }
}
