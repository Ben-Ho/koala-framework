<?php
class Vpc_Menu_Expanded_Component extends Vpc_Menu_Abstract
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['menuModel'] = 'Vpc_Menu_Expanded_MenuModel';
        return $ret;
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();

        $ret['menu'] = $this->_getMenuData();
        foreach ($ret['menu'] as $m) {
            $m->submenu = $this->_getMenuData($m);
        }
        $ret['level'] = $this->_getSetting('level');

        return $ret;
    }
}
