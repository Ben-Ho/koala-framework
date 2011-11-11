<?php
class Kwf_Util_ClearCache
{
    const MODE_CLEAR = 'clear';
    const MODE_IMPORT = 'import';

    /**
     * @return Kwf_Util_ClearCache
     */
    public function getInstance()
    {
        static $i;
        if (!isset($i)) {
            $c = Kwf_Registry::get('config')->clearCacheClass;
            if (!$c) $c = 'Kwf_Util_ClearCache';
            $i = new $c();
        }
        return $i;
    }

    public final function getCacheDirs($mode = self::MODE_CLEAR)
    {
        return $this->_getCacheDirs($mode);
    }

    protected function _getCacheDirs($mode = self::MODE_CLEAR)
    {
        $ret = array();
        foreach (new DirectoryIterator('cache') as $d) {
            if ($d->isDir() && substr($d->getFilename(), 0, 1) != '.') {
                if ($d->getFilename() == 'searchindex') continue;
                if ($d->getFilename() == 'fulltext') continue;
                $ret[] = $d->getFilename();
            }
        }
        if (Kwf_Registry::get('config')->server->cacheDirs) {
            foreach (Kwf_Registry::get('config')->server->cacheDirs as $d) {
                if (substr($d, -2)=='/*') {
                    foreach (new DirectoryIterator(substr($d, 0, -1)) as $i) {
                        if ($i->isDir() && substr($i->getFilename(), 0, 1) != '.') {
                            $ret[] = substr($d, 0, -1).$i->getFilename();
                        }
                    }
                } else {
                    $ret[] = $d;
                }
            }
        }
        return $ret;
    }

    public function getDbCacheTables()
    {
        $ret = array();
        try {
            if (!Zend_Registry::get('db')) return $ret;
        } catch (Exception $e) {
            return $ret;
        }
        $tables = Zend_Registry::get('db')->fetchCol('SHOW TABLES');
        foreach ($tables as $table) {
            if (substr($table, 0, 6) == 'cache_') {
                $ret[] = $table;
            }
        }
        return $ret;
    }

    public function getTypes()
    {

        $types = array('all');
        if (class_exists('Memcache')) $types[] = 'memcache';
        if (extension_loaded('apc')) $types[] = 'apc';
        if (extension_loaded('apc')) {
            $types[] = 'optcode';
        }
        $types[] = 'setup';
        $types = array_merge($types, $this->getCacheDirs());
        $types = array_merge($types, $this->getDbCacheTables());
        return $types;
    }

    private function _refresh($type)
    {
        if ($type == 'setup') {

            file_put_contents('cache/setup.php', Kwf_Util_Setup::generateCode(Kwf_Setup::$configClass));

        } else if ($type == 'settings') {

            $configClass = Kwf_Setup::$configClass;
            $config = new $configClass(Kwf_Setup::getConfigSection());
            $cacheId = 'config_'.str_replace('-', '_', Kwf_Setup::getConfigSection());
            Kwf_Config_Cache::getInstance()->save($config, $cacheId);

            Kwf_Config_Web::clearInstances();
            Kwf_Registry::set('config', $config);
            Kwf_Registry::set('configMtime', Kwf_Config_Web::getInstanceMtime(Kwf_Setup::getConfigSection()));

        } else if ($type == 'component') {

            Kwc_Abstract::getSettingMtime();

        } else if ($type == 'assets') {

            $loader = new Kwf_Assets_Loader();
            $loader->getDependencies()->getMaxFileMTime(); //this is expensive and gets cached in filesystem

        } else if ($type == 'events') {

            Kwf_Component_Events::getAllListeners();

        } else if ($type == 'users') {

            Kwf_Registry::get('userModel')->synchronize(Kwf_Model_MirrorCache::SYNC_ALWAYS);

        } else if ($type == 'users cleanup') {

            // alle zeilen löschen die zuviel sind in kwf_users
            // nötig für lokale tests
            $db = Kwf_Registry::get('db');
            $dbRes = $db->query('SELECT COUNT(*) `cache_users_count` FROM `cache_users`')->fetchAll();
            if ($dbRes[0]['cache_users_count'] >= 1) {
                $dbRes = $db->query('SELECT COUNT(*) `sort_out_count` FROM `kwf_users`
                        WHERE NOT (SELECT cache_users.id
                                    FROM cache_users
                                    WHERE cache_users.id = kwf_users.id
                                    )'
                )->fetchAll();
                $db->query('DELETE FROM `kwf_users`
                        WHERE NOT (SELECT cache_users.id
                                    FROM cache_users
                                    WHERE cache_users.id = kwf_users.id
                                    )'
                );
                return $dbRes[0]['sort_out_count']." rows cleared";
            } else {
                return "skipping: cache_users is empty";
            }

        }
    }

    public final function clearCache($types = 'all', $output = false, $refresh = true, $server = null)
    {
        if ($types == 'all') {
            $types = $this->getTypes();
        } else if ($types == 'component' && extension_loaded('apc')) {
            $types = array('component', 'apc');
        } else {
            if (!is_array($types)) {
                $types = explode(',', $types);
            }
        }
        $this->_clearCache($types, $output, $server);

        if ($refresh) {
            if ($output) echo "\n";

            $refreshTypes = array();
            $refreshTypes[] = 'setup';
            $refreshTypes[] = 'settings';
            if (Kwf_Component_Data_Root::getComponentClass()) {
                $refreshTypes[] = 'component';
            }
            $refreshTypes[] = 'assets';
            if (in_array('cache_component', $this->getDbCacheTables())
                && (in_array('component', $types) || in_array('cache_component', $types))
            ) {
                $refreshTypes[] = 'events';
            }

            try {
                $db = Kwf_Registry::get('db');
            } catch (Exception $e) {
                $db = false;
            }
            if ((in_array('cache_users', $types) || in_array('model', $types)) && $db) {
                $tables = Kwf_Registry::get('db')->fetchCol('SHOW TABLES');
                if (in_array('kwf_users', $tables) && in_array('cache_users', $tables)) {
                    $refreshTypes[] = 'users';
                    if (Kwf_Registry::get('config')->cleanupKwfUsersOnClearCache) {
                        $refreshTypes[] = 'users cleanup';
                    }
                }
            }

            foreach ($refreshTypes as $type) {
                if ($output) echo "Refresh $type".str_repeat('.', 15-strlen($type));
                $t = microtime(true);
                try {
                    $result = $this->_refresh($type);
                    if (!$result) $result= 'OK';
                    $success = true;
                } catch (Exception $e) {
                    if ($output) echo " [\033[01;31mERROR\033[00m] $e\n";
                    continue;
                }
                if ($output) {
                    echo " [\033[00;32m".$result."\033[00m]";
                    echo " ".round((microtime(true)-$t)*1000)."ms";
                    echo "\n";
                }
            }

            $this->_refreshCache($types, $output, $server);
        }
    }

    protected function _refreshCache($types, $output, $server)
    {
    }

    private function _callApcUtil($type, $outputType, $output)
    {
        $result = Kwf_Util_Apc::callClearCacheByCli(array('type' => $type));
        if ($output) {
            if ($result['result']) {
                echo "cleared:     $outputType (".$result['time']."ms) " . $result['message'] . "\n";
            } else {
                $url = $result['url'];
                if ($result['url2']) $url .= ' / ' . $result['url2'];
                echo "error:       $outputType ($url)\n" . $result['message'] . "\n\n";
            }
        }
    }

    protected function _clearCache(array $types, $output, $server)
    {
        if (in_array('memcache', $types)) {
            if ($server) {
                if ($output) echo "ignored:     memcache\n";
            } else {
                $cache = Kwf_Cache::factory('Core', 'Memcached', array(
                    'lifetime'=>null,
                    'automatic_cleaning_factor' => false,
                    'automatic_serialization'=>true));
                $cache->clean();
                if ($output) echo "cleared:     memcache\n";
            }
        }
        if (in_array('apc', $types)) {
            if ($server) {
                if ($output) echo "ignored:     apc\n";
            } else {
                $this->_callApcUtil('user', 'apc', $output);
            }
        }
        if (in_array('optcode', $types)) {
            if ($server) {
                if ($output) echo "ignored:     optcode\n";
            } else {
                $this->_callApcUtil('file', 'optcode', $output);
            }
        }
        if (in_array('setup', $types)) {
            if (file_exists('cache/setup.php')) {
                if ($output) echo "cleared:     cache/setup.php\n";
                unlink('cache/setup.php');
            }
        }
        foreach ($this->getDbCacheTables() as $t) {
            if ($server) {
                if ($output) echo "ignored db:  $t\n";
            } else {
                if (in_array($t, $types) ||
                    (in_array('component', $types) && substr($t, 0, 15) == 'cache_component')
                ) {
                    Zend_Registry::get('db')->query("TRUNCATE TABLE $t");
                    if ($output) echo "cleared db:  $t\n";
                }
            }
        }
        foreach ($this->getCacheDirs() as $d) {
            if (in_array($d, $types)) {
                if (is_dir("cache/$d")) {
                    $this->_removeDirContents("cache/$d", $server);
                } else if (is_dir($d)) {
                    $this->_removeDirContents($d, $server);
                }
                if ($output) echo "cleared dir: $d cache\n";
            }
        }
    }

    private function _removeDirContents($path, $server)
    {
        if ($server) {
            $cmd = "clear-cache-dir --path=$path";
            $cmd = "sshvps $server->user@$server->host $server->dir $cmd";
            $cmd = "sudo -u vps $cmd";
            passthru($cmd, $ret);
            if ($ret != 0) {
                throw new Kwf_ClientException("Clearing remote cache '$path' failed");
            }
        } else {
            $dir = new DirectoryIterator($path);
            foreach ($dir as $fileinfo) {
                if ($fileinfo->isFile() && $fileinfo->getFilename() != '.gitignore') {
                    unlink($fileinfo->getPathName());
                } elseif (!$fileinfo->isDot() && $fileinfo->isDir() && $fileinfo->getFilename() != '.svn') {
                    $this->_removeDirContents($fileinfo->getPathName(), $server);
                    @rmdir($fileinfo->getPathName());
                }
            }
        }
    }
}
