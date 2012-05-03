<?php
class Kwc_Shop_Cart_Checkout_Payment_PayPal_Ipn_Component extends Kwc_Abstract
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['flags']['processInput'] = true;
        return $ret;
    }

    public function processInput(array $postData)
    {
        Kwf_Util_PayPal_Ipn::process();
        exit;
    }
}
