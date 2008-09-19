<?php

class Vps_Mail_Root extends Vpc_Root_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        unset($ret['generators']);
        $ret['generators']['both'] = array(
            'component' => 'Vps_Mail_Both_Component',
            'class' => 'Vps_Component_Generator_Static'
        );
        $ret['generators']['notpl'] = array(
            'component' => 'Vps_Mail_NoTpl_Component',
            'class' => 'Vps_Component_Generator_Static'
        );
        $ret['generators']['htmlonly'] = array(
            'component' => 'Vps_Mail_HtmlOnly_Component',
            'class' => 'Vps_Component_Generator_Static'
        );
        $ret['generators']['txtonly'] = array(
            'component' => 'Vps_Mail_TxtOnly_Component',
            'class' => 'Vps_Component_Generator_Static'
        );
        return $ret;
    }
}
