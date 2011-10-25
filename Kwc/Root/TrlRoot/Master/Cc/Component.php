<?php
class Kwc_Root_TrlRoot_Master_Cc_Component extends Kwc_Chained_Cc_Component
{
    public static function getSettings($masterComponentClass)
    {
        $ret = parent::getSettings($masterComponentClass);
        $ret['flags']['hasLanguage'] = true;
        return $ret;
    }

    public function getLanguage()
    {
        return $this->getData()->chained->getComponent()->getLanguage();
    }
}
