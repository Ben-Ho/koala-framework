<?php
class Vpc_Shop_AddToCart_OrderProductData extends Vpc_Shop_AddToCartAbstract_OrderProductData
{
    public function getPrice($orderProduct)
    {
        return $orderProduct->getParentRow('ProductPrice')->price * $orderProduct->amount;
    }

    public function getAmount($orderProduct)
    {
        return $orderProduct->amount;
    }

    public function getProductText($orderProduct)
    {
        $product = $orderProduct->getParentRow('ProductPrice')->getParentRow('Product');
        return $product->__toString();
    }

    public function getAdditionalOrderData($row)
    {
        $ret = parent::getAdditionalOrderData($row);
        $ret[] = array(
            'class' => 'amount',
            'name' => trlVps('Amount'),
            'value' => $row->amount
        );
        return $ret;
    }

    public function alterBackendOrderForm(Vpc_Shop_AddToCartAbstract_FrontendForm $form)
    {
        $component = null;
        foreach (Vpc_Abstract::getComponentClasses() as $c) {
            if (is_instance_of($c, 'Vpc_Babytuch_Shop_Products_Detail_Component')) {
                $generators = Vpc_Abstract::getSetting($c, 'generators');
                foreach ($generators['addToCart']['component'] as $key => $class) {
                    if ($class == $this->_class) $component = $key;
                }
            }
        }
        $m = Vps_Model_Abstract::getInstance('Vpc_Shop_Products');
        $s = $m->select();
        $s->whereEquals('visible', 1);
        if ($component) $s->whereEquals('component', $component);
        $s->order('pos');
        $data = array();
        foreach ($m->getRows($s) as $product) {
            $data[] = array(
                $product->current_price_id,
                $product->__toString().' ('.$product->current_price.' €)'
            );
        }
        $form->prepend(new Vps_Form_Field_Select('shop_product_price_id', trlVps('Product')))
            ->setValues($data)
            ->setAllowBlank(false);
    }
}
