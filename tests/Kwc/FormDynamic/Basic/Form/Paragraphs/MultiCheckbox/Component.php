<?php
class Kwc_FormDynamic_Basic_Form_Paragraphs_MultiCheckbox_Component extends Kwc_Form_Field_MultiCheckbox_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['ownModel'] = 'Kwc_FormDynamic_Basic_Form_Paragraphs_MultiCheckbox_TestModel';
        return $ret;
    }
}
