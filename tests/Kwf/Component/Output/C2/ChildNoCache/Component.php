<?php
class Kwf_Component_Output_C2_ChildNoCache_Component extends Kwc_Abstract_Composite_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['viewCache'] = false;
        return $ret;
    }
}
?>