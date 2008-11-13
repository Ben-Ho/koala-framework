<?php
class Vpc_Shop_Cart_Checkout_Confirm_Component extends Vpc_Abstract
{
    private $_order;
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['flags']['processInput'] = true;
        $ret['cssClass'] = 'webStandard';
        $ret['viewCache'] = false;
        return $ret;
    }

    public function processInput($data)
    {
        $this->_order = Vps_Model_Abstract::getInstance('Vpc_Shop_Cart_Orders')->getCartOrder();
        if (!$this->_order || !$this->_order->data) {
            throw new Vps_Exception_AccessDenied("No Order exists");
        }
        $mail = $this->_getMail();
        $mail->send();

        $this->_order->status = 'ordered';
        $this->_order->date = new Zend_Db_Expr('NOW()');
        $this->_order->save();

    }

    protected function _getMail()
    {
        $mail = new Vps_Mail($this);
        $mail->order = $this->_order;
        $mail->products = $this->_order->getChildRows('Products');
        $mail->addTo($this->_order->email);
        return $mail;
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();

        $ret['order'] = $this->_order;
        $ret['orderProducts'] = $this->_order->getChildRows('Products');

        return $ret;
    }
}
