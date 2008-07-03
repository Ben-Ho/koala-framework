<?php
function p($src, $Type = 'LOG')
{
    $isToDebug = false;
    if ($Type != 'ECHO' && class_exists('FirePHP') && FirePHP::getInstance()) {
        if (is_object($src) && method_exists($src, 'toArray')) {
            $src = $src->toArray();
        } else if (is_object($src)) {
            $src = (array)$src;
        }
        //wenn FirePHP nicht aktiv im browser gibts false zurück
        if (FirePHP::getInstance()->fb($src, $Type)) return;
    }
    if (is_object($src) && method_exists($src, 'toDebug')) {
        $isToDebug = true;
        $src = $src->toDebug();
    }
    if (is_object($src) && method_exists($src, '__toString')) {
        $src = $src->__toString();
    }
    if ($isToDebug) {
        echo $src;
    } else if (function_exists('xdebug_var_dump')
        && !($src instanceof Zend_Db_Select ||
                $src instanceof Exception)) {
        xdebug_var_dump($src);
    } else {
        if (!isset($_SERVER['SHELL'])) echo "<pre>";
        var_dump($src);
        if (!isset($_SERVER['SHELL'])) echo "</pre>";
    }
    if (function_exists('debug_backtrace')) {
        $bt = debug_backtrace();
        $i = 0;
        if ($bt[1]['function'] == 'd') $i = 1;
        echo $bt[$i]['file'].':'.$bt[$i]['line'];
        if (!isset($_SERVER['SHELL'])) echo "<br />";
        echo "\n";
    }
}

function d($src)
{
    p($src, 'ECHO');
    exit;
}

function _btString($bt)
{
    $ret = '';
    if (isset($bt['class'])) {
        $ret .= $bt['class'].'::';
    }
    if (isset($bt['function'])) {
        $ret .= $bt['function'].'('._btArgsString($bt['args']).')';
    }
    return $ret;
}
function _btArgsString($args)
{
    $ret = array();
    foreach ($args as $arg) {
        if (is_object($arg)) {
            $ret[] = get_class($arg);
        } else if (is_array($arg)) {
            $arrayString = array();
            foreach ($arg as $k=>$i) {
                $s = '';
                if (!is_int($k)) {
                    $s = $k.'=>';
                }
                if (is_array($i)) {
                    $arrayString[] = $s.'array('._btArgsString($i).')';
                } else if (is_object($i)) {
                    $arrayString[] = $s.get_class($i);
                } else if (is_null($i)) {
                    $arrayString[] = $s.'null';
                } else {
                    $arrayString[] = $s.$i;
                }
            }
            $ret[] = 'array('.implode(', ', $arrayString).')';
        } else if (is_null($arg)) {
            $ret[] = 'null';
        } else if (is_string($arg)) {
            $ret[] = '"'.$arg.'"';
        } else {
            $ret[] = $arg;
        }
    }
    return implode(', ', $ret);
}
function bt()
{
    $bt = debug_backtrace();
    unset($bt[0]);
    $out = array(array('File', 'Line', 'Function', 'Args'));
    foreach ($bt as $i) {
        $out[] = array(
            $i['file'], $i['line'],
            isset($i['function']) ? $i['function'] : null,
            _btArgsString($i['args']),
        );
    }
    p(array('Backtrace for '._btString($bt[1]), $out), 'TABLE');
}

function hlp($string){
    return Zend_Registry::get('hlp')->hlp($string);
}

function hlpVps($string){
    return Zend_Registry::get('hlp')->hlpVps($string);
}

function trl($string, $text = array())
{
    return Zend_Registry::get('trl')->trl($string, $text, Vps_Trl::SOURCE_WEB);
}

function trlc($context, $string, $text = array()) {
    return Zend_Registry::get('trl')->trlc($context, $string, $text, Vps_Trl::SOURCE_WEB);
}

function trlp($single, $plural, $text =  array()) {
    return Zend_Registry::get('trl')->trlp($single, $plural, $text, Vps_Trl::SOURCE_WEB);
}

function trlcp($context, $single, $plural = null, $text = array()){
    return Zend_Registry::get('trl')->trlcp($context, $single, $plural, $text, Vps_Trl::SOURCE_WEB);
}

function trlVps($string, $text = array()){
    return Zend_Registry::get('trl')->trl($string, $text, Vps_Trl::SOURCE_VPS);
}

function trlcVps($context, $string, $text = array()){
    return Zend_Registry::get('trl')->trlc($context, $string, $text, Vps_Trl::SOURCE_VPS);
}

function trlpVps($single, $plural, $text =  array()){
    return Zend_Registry::get('trl')->trlp($single, $plural, $text, Vps_Trl::SOURCE_VPS);
}

function trlcpVps($context, $single, $plural, $text = array()){
    return Zend_Registry::get('trl')->trlcp($context, $single, $plural, $text, Vps_Trl::SOURCE_VPS);
}

class Vps_Setup
{
    public static function setUp()
    {
        require_once 'Vps/Loader.php';
        Vps_Loader::registerAutoload();

        Zend_Registry::setClassName('Vps_Registry');

        error_reporting(E_ALL);
        date_default_timezone_set('Europe/Berlin');
        set_error_handler(array('Vps_Debug', 'handleError'), E_ALL);

        $ip = get_include_path();
        foreach (Zend_Registry::get('config')->includepath as $p) {
            $ip .= PATH_SEPARATOR . $p;
        }
        set_include_path($ip);

        if (Zend_Registry::get('config')->debug->benchmark) {
            Vps_Benchmark::enable();
            $GLOBALS['renderedCounter'] = array('cached' => array(), 'notcached' => array());
            $GLOBALS['getComponentByIdCalled'] = array();
        }

        Zend_Registry::set('requestNum', ''.floor(microtime(true)*100));
        if (Zend_Registry::get('config')->debug->firephp && !isset($_SERVER['SHELL'])) {
            ob_start();
            require_once 'FirePHPCore/FirePHP.class.php';
            FirePHP::init();
        }
        if (Zend_Registry::get('config')->debug->querylog && !isset($_SERVER['SHELL'])) {
            header('X-Vps-RequestNum: '.Zend_Registry::get('requestNum'));
        }
        register_shutdown_function(array('Vps_Setup', 'shutDown'));

        if (isset($_POST['PHPSESSID'])) {
            //für swfupload
            Zend_Session::setId($_POST['PHPSESSID']);
        }

        $frontendOptions = array('automatic_serialization' => true);
        $backendOptions  = array('cache_dir' => 'application/cache/table');
        $cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
        Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
    }

    public static function shutDown()
    {
        if (Zend_Registry::get('config')->debug->querylog && !isset($_SERVER['SHELL'])) {
            header('X-Vps-DbQueries: '.Vps_Db_Profiler::getCount());
        }
    }

    public static function createDb()
    {
        $dao = Zend_Registry::get('dao');
        return $dao->getDb();
    }

    public static function createDao()
    {
        return new Vps_Dao(new Zend_Config_Ini('application/config.db.ini', 'database'));
    }

    public static function getConfigSection()
    {
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';

        //www abschneiden damit www.test und www.preview usw auch funktionieren
        if (substr($host, 0, 4)== 'www.') $host = substr($host, 4);

        if (isset($_SERVER['PWD'])) {
            //wenn über kommandozeile aufgerufen
            $path = $_SERVER['PWD'];
        } else {
            $path = $_SERVER['SCRIPT_FILENAME'];
        }
        if (preg_match('#/www/(usr|public)/([0-9a-z-]+)/#', $path, $m)) {
	    if ($m[2]=='vps-projekte') return 'vivid';
            return $m[2];
        } else if (substr($host, 0, 4)=='dev.') {
            return 'dev';
        } else if (substr($host, 0, 5)=='test.' ||
                   substr($path, 0, 17) == '/docs/vpcms/test.' ||
                   substr($path, 0, 21) == '/docs/vpcms/www.test.') {
            return 'test';
        } else if (substr($host, 0, 8)=='preview.') {
            return 'preview';
        } else {
            return 'production';
        }
    }

    public static function createConfig()
    {
        $section = self::getConfigSection();

        $vpsSection = $webSection = 'vivid';

        $webConfigFull = new Zend_Config_Ini('application/config.ini', null);
        if (isset($webConfigFull->$section)) {
            $webSection = $section;
        }

        $vpsConfigFull = new Zend_Config_Ini(VPS_PATH.'/config.ini', null);
        if (isset($vpsConfigFull->$section)) {
            $vpsSection = $section;
        }

        $webConfig = new Zend_Config_Ini('application/config.ini', $webSection);

        $vpsConfig = new Zend_Config_Ini(VPS_PATH.'/config.ini', $vpsSection,
                        array('allowModifications'=>true));
        $vpsConfig->merge($webConfig);

        $v = $vpsConfig->application->vps->version;
        if (preg_match('#tags/vps/([^/]+)/config\\.ini#', $v, $m)) {
            $v = $m[1];
        } else if (preg_match('#branches/vps/([^/]+)/config\\.ini#', $v, $m)) {
            $v = $m[1];
        } else if (preg_match('#trunk/vps/config\\.ini#', $v, $m)) {
            $v = 'trunk';
        }
        $vpsConfig->application->vps->version = $v;
        if (preg_match('/Revision: ([0-9]+)/', $vpsConfig->application->vps->revision, $m)) {
            $vpsConfig->application->vps->revision = (int)$m[1];
        }
        return $vpsConfig;
    }

    public function dispatchVpc()
    {
        if (!isset($_SERVER['REDIRECT_URL'])) return;

        $uri = substr($_SERVER['REDIRECT_URL'], 1);
        $i = strpos($uri, '/');
        if ($i) $uri = substr($uri, 0, $i);
        if (!in_array($uri, array('media', 'vps', 'admin', 'assets'))) {
            $requestUrl = $_SERVER['REDIRECT_URL'];

            $root = Vps_Component_Data_Root::getInstance();
            $data = $root->getPageByPath($requestUrl);
            if (!$data) {
                header('HTTP/1.1 404 Not Found');
                $view = new Vps_View();
                $view->requestUri = $requestUrl;
                echo $view->render('error404.tpl');
                exit;
            }
            $root->setCurrentPage($data);
            if ($data->url != $requestUrl) {
                header('Location: '.$data->url);
                exit;
            }
            $page = $data->getComponent();
            $page->sendContent($page);

            Vps_Benchmark::output();

            exit;
        }
    }

    public static function dispatchMedia()
    {
        if (!isset($_SERVER['REDIRECT_URL'])) return;

        $urlParts = explode('/', substr($_SERVER['REDIRECT_URL'], 1));
        if (is_array($urlParts) && $urlParts[0] == 'media') {
            $params['table'] = $urlParts[1];
            $params['id'] = $urlParts[2];
            $params['rule'] = $urlParts[3];
            $params['type'] = $urlParts[4];
            $params['checksum'] = $urlParts[5];
            $params['filename'] = $urlParts[6];

            $download = false;
            $checksum = md5(
                Vps_Db_Table_Row::FILE_PASSWORD .
                $params['table'] .
                $params['id'] .
                $params['rule'] .
                $params['type']
            );
            if ($checksum != $params['checksum']) {
                $checksum = md5(
                    Vps_Db_Table_Row::FILE_PASSWORD_DOWNLOAD .
                        $params['table'] .
                        $params['id'] .
                        $params['rule'] .
                        $params['type']
                );
                $download = true;
            }
            if ($checksum != $params['checksum']) {
                throw new Vps_Controller_Action_Web_Exception('Access to file not allowed.');
            }

            $class = $params['table'];
            $type = $params['type'];
            $id = explode(',', $params['id']);
            $rule = $params['rule'];
            if ($rule == 'default') { $rule = null; }

            // TODO: Cachen ohne Datenbankabfragen
            if (class_exists($class) && is_subclass_of($class, 'Vpc_Abstract')) {
                $tableClass = Vpc_Abstract::getSetting($class, 'tablename');
                $table = new $tableClass(array('componentClass' => $class));
            } else {
                $table = new $class();
            }
            $row = $table->find($id)->current();
            if (!$row) {
                throw new Vps_Exception('File not found.');
            }
            $fileRow = $row->findParentRow('Vps_Dao_File', $rule);
            if (!$fileRow) {
                throw new Vps_Exception('No File uploaded.');
            }
            $target = $row->getFileSource($rule, $type);

            $downloadFilename = false;
            if ($download) {
                $downloadFilename = $params['filename'];
            }
            Vps_Media_Output::output($target, $fileRow->mime_type, $downloadFilename);
        }
    }
}
