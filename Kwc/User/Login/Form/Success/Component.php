<?php
class Kwc_User_Login_Form_Success_Component extends Kwc_Form_Success_Component
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
        $ret['redirectTo'] = $this->_getRedirectToPage();
        return $ret;
    }

    protected function _getRedirectToPage()
    {
        if (is_instance_of($this->getData()->getPage()->componentClass, 'Kwc_User_Login_Component')) {
            $user = Kwf_Registry::get('userModel')->getAuthedUser();
            $userDir = Kwf_Component_Data_Root::getInstance()
                ->getComponentByClass(
                    'Kwc_User_Directory_Component',
                    array('subroot' => $this->getData())
                );
            if ($userDir) {
                return $userDir->getChildComponent('_' . $user->id);
            } else {
                return Kwf_Component_Data_Root::getInstance()
                    ->getChildPage(array('home' => true, 'subroot'=>$this->getData()));
            }
        } else {
            return $this->getData()->getPage();
        }
    }
}