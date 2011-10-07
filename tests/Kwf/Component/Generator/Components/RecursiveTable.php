<?php
class Kwf_Component_Generator_Components_RecursiveTable extends Kwc_Abstract
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['static'] = array(
            'class' => 'Kwf_Component_Generator_Static',
            'component' => 'Kwc_Basic_Image_Component'
        );
        $ret['generators']['staticpage'] = array(
            'class' => 'Kwf_Component_Generator_Page_Static',
            'component' => 'Kwc_Basic_Html_Component'
        );
        return $ret;
    }
}
?>