<?php
class Kwc_Basic_TextSessionModel_Link_Intern_Component extends Kwc_Basic_LinkTag_Intern_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['ownModel'] = 'Kwc_Basic_TextSessionModel_Link_Intern_TestModel';
        return $ret;
    }
}
