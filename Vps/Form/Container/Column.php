<?php
class Vps_Form_Container_Column extends Vps_Form_Container_Abstract
{
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->setBaseCls('x-plain');
        $this->setStyle('margin: 0px 10px;');
    }
}
