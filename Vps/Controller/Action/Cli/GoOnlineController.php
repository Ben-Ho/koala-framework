<?php
class Vps_Controller_Action_Cli_GoOnlineController extends Vps_Controller_Action_Cli_Abstract
{
    public static function getHelp()
    {
        if (!Vps_Controller_Action_Cli_TagController::getProjectName()) return null;
        if (Vps_Registry::get('config')->server->host != 'vivid') return null;
        return "go online";
    }

    public static function getHelpOptions()
    {
        $ret = Vps_Controller_Action_Cli_TagController::getHelpOptions();
        $ret[] = array('param' => 'skip-test');
        $ret[] = array('param' => 'skip-prod');
        $ret[] = array('param' => 'skip-check');
        $ret[] = array('param' => 'skip-notify');
        return $ret;
    }

    private function _systemSshVps($cmd, $config)
    {
        $sshHost = $config->server->user.'@'.$config->server->host;
        $sshDir = $config->server->dir;
        $cmd = "sshvps $sshHost $sshDir $cmd";
        $cmd = "sudo -u vps $cmd";
        return $this->_systemCheckRet($cmd);
    }

    public function indexAction()
    {
        Zend_Session::start(); //wegen tests

        $prodConfig = new Zend_Config_Ini('application/config.ini', 'production');
        if (!$prodConfig || !$prodConfig->server->host || !$prodConfig->server->dir) {
            throw new Vps_ClientException("Prod-Server not configured");
        }

        $testConfig = new Zend_Config_Ini('application/config.ini', 'test');
        if (!$testConfig || !$testConfig->server->host || !$testConfig->server->dir) {
            throw new Vps_ClientException("Test-Server not configured");
        }
        if ($testConfig->server->dir == $prodConfig->server->dir) {
            throw new Vps_ClientException("Test-Server not configured, same dir as production");
        }
        $vpsVersion = $this->_getParam('vps-version');
        $webVersion = $this->_getParam('web-version');

        echo "\n\n*** [00/13] ueberpruefe auf nicht eingecheckte dateien\n";

        if ($this->_getParam('skip-check')) {
            echo "(uebersprungen)\n";
        } else {
            echo "lokaler-server:\n";
            Vps_Controller_Action_Cli_SvnUpController::checkForModifiedFiles(true);
            echo "test-server:\n";
            $this->_systemSshVps("svn-up check-for-modified-files", $testConfig);
            echo "prod-server:\n";
            $this->_systemSshVps("svn-up check-for-modified-files", $prodConfig);
        }

        echo "\n\n*** [01/13] vps-tag erstellen\n";
        Vps_Controller_Action_Cli_TagController::createVpsTag($vpsVersion);

        echo "\n\n*** [02/13] web-tag erstellen\n";
        Vps_Controller_Action_Cli_TagController::createWebTag($webVersion);

        echo "\n\n*** [03/13] vps tag auschecken\n";
        $this->_systemSshVps("tag-checkout vps-checkout --version=$vpsVersion", $testConfig);

        echo "\n\n*** [04/13] test: vps-version anpassen\n";
        $this->_systemSshVps("tag-checkout vps-use --version=$vpsVersion", $testConfig);

        echo "\n\n*** [05/13] test: web tag switchen\n";
        $this->_systemSshVps("tag-checkout web-switch --version=$webVersion", $testConfig);

        echo "\n\n*** [06/13] prod daten auf test uebernehmen\n";
        $this->_systemSshVps("import", $testConfig);

        echo "\n\n*** [07/13] test: unit-tests laufen lassen\n";
        $skipTest = ($this->_getParam('skip-test') || $this->_getParam('skip-tests'));
        if ($skipTest) {
            echo "(uebersprungen)\n";
        } else {
            Vps_Controller_Action_Cli_TestController::initForTests();
            $suite = new Vps_Test_TestSuite();
            $runner = new PHPUnit_TextUI_TestRunner;

            Vps_Registry::set('testDomain', $testConfig->server->domain);
            Vps_Registry::set('testServerConfig', $testConfig);

            $arguments = array();
            $arguments['colors'] = true;
            $arguments['stopOnFailure'] = true;
            $result = $runner->doRun($suite, $arguments);
            if (!$result->wasSuccessful()) {
                $this->_systemSshVps("tag-checkout web-switch --version=trunk", $testConfig);
                $this->_systemSshVps("tag-checkout vps-use --version=trunk", $testConfig);
                throw new Vps_ClientException("Tests failed");
            }
        }

        echo "\n\n*** [08/13] test: web zurueck auf trunk switchen\n";
        $this->_systemSshVps("tag-checkout web-switch --version=trunk", $testConfig);

        echo "\n\n*** [09/13] test: vps-version zurueck auf trunk anpassen\n";
        $this->_systemSshVps("tag-checkout vps-use --version=trunk", $testConfig);

        $doneTodos = array();
        if ($this->_getParam('skip-prod')) {
            echo "\n\n*** [10/13] prod: (uebersprungen)\n";
        } else {

            echo "\n\n*** [10/13] prod: erstelle datenbank backup\n";
            $this->_systemSshVps("import backup-db", $prodConfig);

            echo "\n\n*** [11/13] prod: vps-version anpassen\n";
            $this->_systemSshVps("tag-checkout vps-use --version=$vpsVersion", $prodConfig);

            echo "\n\n*** [12/13] prod: web tag switchen\n";
            $this->_systemSshVps("tag-checkout web-switch --version=$webVersion", $prodConfig);

            echo "\n\n*** [13/13] prod: update ausführen\n";
            $this->_systemSshVps("update", $prodConfig);

            $projectIds = array();
            if (Vps_Registry::get('config')->todo->projectIds) {
                $projectIds = Vps_Registry::get('config')->todo->projectIds->toArray();
            }
            if ($projectIds) {
                $m = Vps_Model_Abstract::getInstance('Vps_Util_Model_Todo');
                $s = $m->select()
                        ->whereEquals('project_id', $projectIds)
                        ->whereNotEquals('status', 'prod');
                foreach ($m->getRows($s) as $todo) {
                    if (!$todo->done_revision) continue;
                    $project = Vps_Controller_Action_Cli_TagController::getProjectName();
                    if ($this->_hasRevisionInHistory($project, $webVersion, $todo->done_revision)
                        || $this->_hasRevisionInHistory('vps', $vpsVersion, $todo->done_revision)
                    ) {
                        $todo->status = 'prod';
                        $todo->prod_date = date('Y-m-d');
                        $todo->save();
                        echo "\ntodo #{$todo->id} ({$todo->title}) als auf prod markiert";
                        $doneTodos[] = $todo;
                    }
                }
                echo "\n";
            }
        }

        if (!$this->_getParam('skip-prod')) {
            echo "\n";
            $cfg = Vps_Registry::get('config');
            if (isset($_SERVER['USER'])) {
                $user = ucfirst($_SERVER['USER']);
            } else {
                $user = 'Jemand';
            }
            $msg = "$user hat soeben {$cfg->application->name} mit Version $webVersion (Vps $vpsVersion) online gestellt.\n";
            if ($skipTest) {
                $msg .= "\nUnit-Tests wurden NICHT ausgeführt.";
            } else {
                $msg .= "\nUnit-Tests wurden erfolgreich ausgeführt.";
            }
            if (count($doneTodos)) {
                $msg .= "\n\nFolgende Todos wurden erledigt:";
                foreach ($doneTodos as $todo) {
                    $msg .= "\ntodo #{$todo->id} ({$todo->title})";
                }
            }
            $jids = array();
            foreach ($cfg->developers as $user) {
                if ($user->notifyGoOnline && $user->jid) {
                    echo "\nsende jabber nachricht an $user->jid";
                    $jids[] = $user->jid;
                }
            }
            if ($jids) {
                Vps_Util_Jabber_SendMessage::send($jids, $msg);
            }
        }

        echo "\n\n\n\033[32mF E R T I G ! ! !\033[0m\n";

        exit;
    }
    private function _hasRevisionInHistory($project, $version, $revision)
    {
        $log = `svn log --xml --revision $revision http://svn/tags/$project/$version`;
        $log = new SimpleXMLElement($log);
        if (count($log->logentry)) return true;
    }
}
