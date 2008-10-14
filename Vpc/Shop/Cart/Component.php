<?php
class Vpc_Shop_Cart_Component extends Vpc_Directories_Item_Directory_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['child']['component']['form'] = 'Vpc_Shop_Cart_Form_Component';
        $ret['generators']['detail']['class'] = 'Vpc_Shop_Cart_Generator';
        $ret['generators']['detail']['component'] = 'Vpc_Shop_Cart_Detail_Component';
        $ret['generators']['detail']['model'] = 'Vpc_Shop_Cart_OrderProducts';
        $ret['generators']['checkout'] = array(
            'class' => 'Vps_Component_Generator_Page_Static',
            'component' => 'Vpc_Shop_Cart_Checkout_Component',
            'name' => trlVps('Checkout')
        );
        $ret['viewCache'] = false;
        $ret['cssClass'] = 'webStandard';
        return $ret;
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        $ret['countProducts'] = $this->getData()->countChildComponents(array('generator'=>'detail'));
        $ret['checkout'] = $this->getData()->getChildComponent('_checkout');
        return $ret;
    }
}
