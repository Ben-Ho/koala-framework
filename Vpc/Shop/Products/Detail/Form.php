<?php
class Vpc_Shop_Products_Detail_Form extends Vps_Form
{
    protected function _init()
    {
        parent::_init();
        $this->add(new Vps_Form_Field_TextField('title', trlVps('Title')));

        $mf = $this->add(new Vps_Form_Field_MultiFields('Prices'));
        $mf->setModel(Vps_Model_Abstract::getInstance('Vpc_Shop_ProductPrices'));
        $mf->setPosition(false);
        $fs = $mf->fields->add(new Vps_Form_Container_FieldSet(trlVps('Price')));
            $fs->add(new Vps_Form_Field_NumberField('price', trlVps('Price')))
                ->setAllowBlank(false);
            $fs->add(new Vps_Form_Field_DateTimeField('valid_from', trlVps('Valid From')))
                ->setAllowBlank(false);

        $this->add(new Vps_Form_Field_Checkbox('visible', trlVps('Visible')));
        $this->add(Vpc_Abstract_Form::createComponentForm('shopProducts_{0}-image'));
        $this->add(Vpc_Abstract_Form::createComponentForm('shopProducts_{0}-text'));
    }
}
