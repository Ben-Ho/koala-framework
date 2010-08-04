<?php
class Vpc_List_Switch_Component extends Vpc_Abstract_List_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['assets']['files'][] = 'vps/Vpc/List/Switch/Component.js';
        $ret['assets']['dep'][] = 'ExtCore';
        $ret['generators']['child']['component'] = 'Vpc_List_Switch_Preview_Component';
        $ret['placeholder']['prev'] = trlVps('previous');
        $ret['placeholder']['next'] = trlVps('next');
        $ret['previewCssClass'] = '';

        // transition kann auch auf false gesetzt werden um "direkt" umzuschalten
        $ret['transition'] = array(
            'type'               => 'fade',   // possible values: fade
            'duration'           => 0.8,      // use with types: fade
            'easingOut'          => 'easeIn', // use with types: fade
            'easingIn'           => 'easeIn'  // use with types: fade
        );
        $ret['hideArrowsAtEnds'] = false; // false = wenn man beim letzten element ankommt und auf "weiter" klickt, kommt man wieder zum ersten
        return $ret;
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        $ret['previewCssClass'] = $this->_getSetting('previewCssClass');
        $ret['options']['transition'] = $this->_getSetting('transition');
        $ret['options']['hideArrowsAtEnds'] = $this->_getSetting('hideArrowsAtEnds');
        return $ret;
    }
}
