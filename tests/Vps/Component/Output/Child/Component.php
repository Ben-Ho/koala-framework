<?php
class Vps_Component_Output_Child_Component extends Vpc_Abstract_Composite_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['child']['component'] = array(
            'child' => 'Vps_Component_Output_ChildChild_Component'
        );
        $ret['plugins'] = array('Vps_Component_Output_Plugin', 'Vps_Component_Output_Plugin');
        return $ret;
    }
    
    public function hasContent()
    {
        return true;
    }
}
?>