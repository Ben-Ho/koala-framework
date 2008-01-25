<?php
class Vpc_Composite_Downloads_Component extends Vpc_Abstract_List_Component
{
    public static function getSettings()
    {
        $settings = parent::getSettings();
        $settings['childComponentClasses']['child'] = 'Vpc_Basic_Download_Component';
        $settings['componentName'] = 'Downloads';

        return $settings;
    }
}
