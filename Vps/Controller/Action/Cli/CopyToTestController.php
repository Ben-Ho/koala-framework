<?php
class Vps_Controller_Action_Cli_CopyToTestController extends Vps_Controller_Action_Cli_Abstract
{
    public static function getHelp()
    {
        return "copy from prod to test";
    }

    public static function getHelpOptions()
    {
        $sections = self::_getConfigSections();
        if (in_array('production', $sections)) {
            unset($sections[array_search('production', $sections)]);
        }
        return array(
            array(
                'param'=> 'server',
                'value'=> $sections,
                'valueOptional' => false,
                'help' => 'where to copy from prod'
            )
        );
    }

    private function _systemSshVps($cmd)
    {
        $cmd = "sshvps $this->_sshHost $this->_sshDir $cmd";
        $cmd = "sudo -u www-data $cmd";
        return $this->_systemCheckRet($cmd);
    }

    public function indexAction()
    {
        $config = new Zend_Config_Ini('application/config.ini', $this->_getParam('server'));

        $this->_sshHost = $config->server->user.'@'.$config->server->host;
        $this->_sshDir = $config->server->dir;

        $this->_systemSshVps("import");
        exit();
    }
}
