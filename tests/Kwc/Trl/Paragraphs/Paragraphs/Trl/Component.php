<?php
class Kwc_Trl_Paragraphs_Paragraphs_Trl_Component extends Kwc_Paragraphs_Trl_Component
{
    public static function getSettings($masterComponentClass)
    {
        $ret = parent::getSettings($masterComponentClass);
        $ret['childModel'] = 'Kwc_Trl_Paragraphs_Paragraphs_Trl_TestModel';
        return $ret;
    }
}
