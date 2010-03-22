<?php
class Vpc_News_Detail_Abstract_Trl_Form extends Vps_Form
{
    public function __construct($directoryClass = null)
    {
        $this->setDirectoryClass($directoryClass);
        parent::__construct('details');
    }

    protected function _initFields()
    {
        parent::_initFields();

        $this->add(new Vps_Form_Field_TextField('title', trlVps('Title')))
            ->setAllowBlank(false)
            ->setWidth(300);
        $this->add(new Vps_Form_Field_TextArea('teaser', trlVps('Teaser')))
            ->setWidth(300)
            ->setHeight(100);
    }
}
