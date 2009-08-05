<?php
class Vpc_Newsletter_Component extends Vpc_Directories_ItemPage_Directory_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['detail']['class'] = 'Vpc_Newsletter_GeneratorDetail';
        $ret['generators']['detail']['component'] = 'Vpc_Newsletter_Detail_Component';

        // wird von der Mail_Redirect gerendered
        $ret['generators']['unsubscribe'] = array(
            'class' => 'Vps_Component_Generator_Static',
            'component' => 'Vpc_Newsletter_Unsubscribe_Component',
            'name' => trlVps('Unsubscribe')
        );

        $ret['modelname'] = 'Vpc_Newsletter_Model';
        $ret['flags']['hasResources'] = true;
        $ret['componentName'] = trlVps('Newsletter');
        return $ret;
    }
}
