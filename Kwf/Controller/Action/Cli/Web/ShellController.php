<?php
class Kwf_Controller_Action_Cli_Web_ShellController extends Kwf_Controller_Action_Cli_Abstract
{
    public static function getHelp()
    {
        return "open shell";
    }
    public static function getHelpOptions()
    {
        return array(
            array(
                'param'=> 'server',
                'value'=> self::_getConfigSections(),
                'allowBlank' => true,
                'help' => 'which server'
            ),
            array(
                'param'=> 'exec',
                'allowBlank' => true,
                'help' => 'command to execute'
            )
        );
    }
    public function indexAction()
    {
        $section = $this->_getParam('server');
        $allSections = self::_getConfigSections();
        if (!$section) {
            if ($this->_getParam('exec')) {
                throw new Kwf_Exception_Client("server required when using exec");
            }
            echo "Choose a server:\n";
            foreach ($allSections as $k=>$i) {
                echo ($k+1).": ".$i."\n";
            }
            $stdin = fopen('php://stdin', 'r');
            $input = fgets($stdin, 3);
            fclose($stdin);
            $input = $input-1;
            if (!isset($allSections[$input])) {
                throw new Kwf_Exception_Client("Invalid server number");
            }
            $section = $allSections[$input];
        }
        if (preg_match('#^([0-9]+)-([0-9]+)$#', $section, $m)) {
            $sections = array();
            for($i=$m[1];$i<=$m[2];$i++) {
                $sections[] = $i;
            }
        } else {
            $sections = explode(',', $section);
        }
        foreach ($sections as $k=>$section) {
            if (is_numeric($section)) {
                $sections[$k] = $allSections[$section-1];
            }
        }
        if (count($sections) > 1 && !$this->_getParam('exec')) {
            throw new Kwf_Exception_Client("can't use multiple sections without exec");
        }
        foreach ($sections as $section) {
            if (count($sections) > 1) {
                echo "executing on $section...\n";
            }
            $config = Kwf_Config_Web::getInstance($section);

            if (!$config->server->host) {
                throw new Kwf_ClientException("No host configured for $section server");
            }

            if ($this->_getParam('user')) {
                $host = $this->_getParam('user').'@'.$config->server->host;
                $host .= ' -p '.$config->server->port;
                $dir = $config->server->dir;

                $cmd = "cd $dir; ";
                $cmd .= Kwf_Util_Git::getAuthorEnvVars().' ';
                if ($this->_getParam('exec')) {
                    $exec = $this->_getParam('exec');
                    $cmd .= $exec;
                } else {
                    $cmd .= "exec bash";
                }
                $cmd = "ssh -t $host ".escapeshellarg($cmd);
            } else {
                $host = $config->server->user.'@'.$config->server->host.':'.$config->server->port;
                $dir = $config->server->dir;

                $cmd = "sudo -u vps ".Kwf_Util_Git::getAuthorEnvVars()." sshvps $host $dir shell";
                if ($this->_getParam('debug')) $cmd .= " --debug";
                if ($this->_getParam('exec')) {
                    $exec = $this->_getParam('exec');
                    //nützlich um sowas tun zu können: kwf shell --server=$SERVER --exec="echo -n \"%KWF_CONFIG_SECTION%\" > config_section"
                    $exec = str_replace('%KWF_CONFIG_SECTION%', $section, $exec);
                    $cmd .= " --exec=".escapeshellarg($exec);
                }
            }
            if ($this->_getParam('debug')) echo $cmd."\n";
            passthru($cmd);
            if (count($sections) > 1) {
                echo "\n";
            }
        }

        $this->_helper->viewRenderer->setNoRender(true);
    }
}
