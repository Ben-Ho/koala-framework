<?php
class Vpc_Shop_Cart_Checkout_Payment_Abstract_Mail_Component extends Vpc_Mail_Editable_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['content']['component'] = 'Vpc_Shop_Cart_Checkout_Payment_Abstract_Mail_Paragraphs_Component';
        $ret['componentName'] = trlVps('Shop Conformation Mail');
        $ret['recipientSources'] = array(
            'ord' => 'Vpc_Shop_Cart_Orders'
        );
        return $ret;
    }

    public function getPlaceholders(Vpc_Mail_Recipient_Interface $o = null)
    {
        $ret = parent::getPlaceholders($o);
        $m = new Vps_View_Helper_Money();
        $ret['total'] = $m->money($o->getTotal());
        $ret['orderNumber'] = $o->getOrderNumber();
        return $ret;
    }
}
