<?php
/**
 * @group Assets
 * @group Assets_Component
 */
 class Vps_Assets_WithTextComponent_Test extends PHPUnit_Framework_TestCase
{
    public function testDebug()
    {
        $rootComponent = 'Vps_Assets_WithTextComponent_Root_Component';

        $config = clone Zend_Registry::get('config');
        $config->debug->menu = false;
        $config->debug->assets->js = true;
        $config->debug->assets->css = true;
        $config->debug->assets->printcss = true;
        $dep = new Vps_Assets_Dependencies($config);

        $type = 'Vps_Assets_WithTextComponent:Test';
        $files = $dep->getAssetUrls($type, 'js', 'web', $rootComponent);
        $this->assertContains('/assets/web-vps/Vps_js/Form/HtmlEditor.js', $files);
    }
}
