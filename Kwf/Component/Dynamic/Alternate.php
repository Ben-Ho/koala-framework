<?php
/**
 * gibt 1 oder 2 zurück
 */
class Kwf_Component_Dynamic_Alternate extends Kwf_Component_Dynamic_Abstract
{
    protected $_modulo = 2;
    public function setArguments($modulo = null)
    {
        if ($modulo) $this->_modulo = $modulo;
    }

    public function getContent()
    {
        return ($this->_info['number'] % $this->_modulo) + 1;
    }
}
