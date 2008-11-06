<?php
class Vps_Controller_Action_Cli_ExportController extends Vps_Controller_Action_Cli_Abstract
{
    public static function getHelp()
    {
        return "update svn online";
    }

    public static function getHelpOptions()
    {
        return array(
            array(
                'param'=> 'server',
                'value'=> self::_getConfigSections(),
                'valueOptional' => false,
                'help' => 'where to update'
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

        $this->_systemSshVps("svn-up");
        exit();
    }
}
