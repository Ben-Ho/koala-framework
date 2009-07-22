<?php
class Vpc_Shop_Products_Directory_Component extends Vpc_Directories_ItemPage_Directory_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['child']['component']['view'] = 'Vpc_Shop_Products_View_Component';

        $ret['generators']['detail']['class'] = 'Vpc_Shop_Products_Directory_Generator';
        $ret['generators']['detail']['component'] = 'Vpc_Shop_Products_Detail_Component';
        $ret['generators']['detail']['dbIdShortcut'] = 'shopProducts_';

        $ret['generators']['addToCart'] = array(
            'class' => 'Vps_Component_Generator_Table',
            'component' => 'Vpc_Shop_Products_Directory_AddToCart_Component'
        );

        $ret['modelname'] = 'Vpc_Shop_Products';

        $ret['componentName'] = trlVps('Shop.Products');
        $ret['flags']['hasResources'] = true;
        return $ret;
    }
}
