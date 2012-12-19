<?php
class Kwc_Menu_IsVisibleDynamic_Test_Component extends Kwc_Abstract
{
    public static $invisibleIds = array(); //modified by test during runtime

    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['flags']['hasIsVisibleDynamic'] = true;
        return $ret;
    }

    public static function isVisibleDynamic($componentId, $componentClass)
    {
        return !in_array($componentId, self::$invisibleIds);
    }
}
