<?php
class Kwc_User_BoxWithoutLogin_Component extends Kwc_User_BoxAbstract_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['showLostPassword'] = true;
        $ret['plugins'][] = 'Kwc_User_BoxWithoutLogin_IsLoggedInPlugin_Component';
        $ret['generators']['child']['component']['loggedIn'] = 'Kwc_User_BoxWithoutLogin_LoggedIn_Component';
        $ret['linkPostfix'] = '';
        return $ret;
    }
    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        $ret['register'] = Kwf_Component_Data_Root::getInstance()
                        ->getComponentByClass(
                            'Kwc_User_Register_Component',
                            array('subroot' => $this->getData())
                        );
        if ($this->_getSetting('showLostPassword')) {
            $ret['lostPassword'] = Kwf_Component_Data_Root::getInstance()
                            ->getComponentByClass(
                                'Kwc_User_LostPassword_Component',
                                array('subroot' => $this->getData())
                            );
        }
        $ret['login'] = Kwf_Component_Data_Root::getInstance()
                        ->getComponentByClass(
                            'Kwc_User_Login_Component',
                            array('subroot' => $this->getData())
                        );
        $ret['linkPostfix'] = $this->_getSetting('linkPostfix');
        return $ret;
    }

    //verschoben in LoggedIn unterkomponente
    protected final function _getLinks() {}
}
