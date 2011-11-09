<?php
class Kwf_Component_Cache_Box_IcRoot_InheritContent_Child_Component extends Kwc_Abstract
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['ownModel'] = 'Kwf_Component_Cache_Box_IcRoot_InheritContent_Child_Model';
        $ret['throwHasContentChangedOnRowColumnsUpdate'] = 'has_content';
        return $ret;
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        $ret['content'] = $this->getRow()->content;
        return $ret;
    }

    public function hasContent()
    {
        return $this->getRow()->has_content;
    }
}
