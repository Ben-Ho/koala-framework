<?php
class Kwc_FormDynamic_Basic_Form_Paragraphs_Checkbox_Component extends Kwc_Form_Field_Checkbox_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['ownModel'] = 'Kwc_FormDynamic_Basic_Form_Paragraphs_Checkbox_TestModel';
        return $ret;
    }
}