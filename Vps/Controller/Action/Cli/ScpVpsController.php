<?php
class Vps_Controller_Action_Cli_ScpVpsController extends Vps_Controller_Action_Cli_Abstract
{
    public static function getHelp()
    {
        return "copy using scp";
    }
    public static function getHelpOptions()
    {
        return array(
            array(
                'param'=> 'server',
                'value'=> self::_getConfigSections(),
                'valueOptional' => false,
                'help' => 'which server'
            ),
            array(
                'param'=> 'file',
                'valueOptional' => false,
                'help' => 'file to copy, must be relative and in vps'
            )

        );
    }
    public function indexAction()
    {
        $file = $this->_getParam('file');
        if (substr($file, 0, 1) == '/') {
            throw new Vps_ClientException('file must be relative');
        }
        if (!is_file(VPS_PATH.'/'.$file)) {
            throw new Vps_ClientException('file not found');
        }
        $p = realpath(VPS_PATH.'/'.$file);
        if (substr($p, 0, strlen(VPS_PATH)) != VPS_PATH) {
            throw new Vps_ClientException('file must be in vps');
        }
        $section = $this->_getParam('server');

        $config = Vps_Config_Web::getInstance($section);


        if (!$config->server->host) {
            throw new Vps_ClientException("No host configured for $section server");
        }

        $host = $config->server->user.'@'.$config->server->host.':'.$config->server->port;
        $dir = $config->server->dir;

        $cmd = "sudo -u vps sshvps $host $dir scp-vps";
        $cmd .= " --file=".escapeshellarg($file);
        if ($this->_getParam('debug')) $cmd .= " --debug";
        if ($this->_getParam('debug')) echo $cmd."\n";
        passthru($cmd);
        $this->_helper->viewRenderer->setNoRender(true);
    }
}
