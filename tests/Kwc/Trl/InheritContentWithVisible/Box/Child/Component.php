<?php
class Kwc_Trl_InheritContentWithVisible_Box_Child_Component extends Kwc_Abstract
{
    public function hasContent()
    {
        if ($this->getData()->componentId == 'root-de-box-child' || $this->getData()->componentId == 'root-de_test_test2_test3-box-child') {
            return true;
        }
        return false;
    }
}
