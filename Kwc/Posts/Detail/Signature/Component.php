<?php
class Kwc_Posts_Detail_Signature_Component extends Kwc_Abstract
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['viewCache'] = false;
        return $ret;
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        $userDir = Kwf_Component_Data_Root::getInstance()
            ->getComponentByClass(
                'Kwc_User_Directory_Component',
                array('subroot' => $this->getData())
            );
        if ($userDir) {
            $ret['user'] = $userDir->getChildComponent('_'.$this->getData()->parent->row->user_id);
        } else {
            $ret['user'] = false;
        }
        return $ret;
    }
}
