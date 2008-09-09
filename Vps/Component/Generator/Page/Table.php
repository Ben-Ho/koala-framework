<?php
class Vps_Component_Generator_Page_Table extends Vps_Component_Generator_PseudoPage_Table
    implements Vps_Component_Generator_Page_Interface, Vps_Component_Generator_PseudoPage_Interface
{
    protected $_idSeparator = '_';
    protected $_maxNameLength;
    protected function _init()
    {
        parent::_init();

        if (isset($this->_maxNameLength)) {
            $this->_settings['maxNameLength'] = $this->_maxNameLength;
        }
        if (!isset($this->_settings['maxNameLength'])) $this->_settings['maxNameLength'] = 100;
    }

    protected function _formatConfig($parentData, $row)
    {
        $data = parent::_formatConfig($parentData, $row);
        $data['isPage'] = true;

        $data['name'] = $this->_getNameFromRow($row);

        if (isset($data['name']) && strlen($data['name']) > $this->_settings['maxNameLength']) {
            $data['name'] = substr($data['name'], 0, $this->_settings['maxNameLength']-3).'...';
        }

        return $data;
    }
}
