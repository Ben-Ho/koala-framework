<?php
class Kwc_Directories_YearMonth_Component extends Kwc_Abstract
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['months'] = array(
            'class' => 'Kwc_Directories_YearMonth_Generator',
            'component' => 'Kwc_Directories_YearMonth_Month_Directory_Component',
            'showInMenu' => true,
            //'model' => null,
        );
        $ret['dateColumn'] = null;
        return $ret;
    }
}
