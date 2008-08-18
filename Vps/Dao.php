<?php
class Vps_Dao
{
    private $_config;
    private $_tables = array();
    private $_db = array();
    private $_pageData = array();

    public function __construct(Zend_Config $config)
    {
        $this->_config = $config;
    }

    public static function getTable($tablename, $config = array())
    {
        static $tables;
        if (!isset($tables[$tablename])) {
            $tables[$tablename] = new $tablename($config);
        }
        return $tables[$tablename];
    }

    public function getDb($db = 'web')
    {
        if (!isset($this->_db[$db])) {
            if (!isset($this->_config->$db)) {
                throw new Vps_Dao_Exception("Connection \"$db\" in config.db.ini not found.
                        Please add $db.host, $db.username, $db.password and $db.dbname under the sction [database].");
            }
            $dbConfig = $this->_config->$db->toArray();
            $this->_db[$db] = Zend_Db::factory('PDO_MYSQL', $dbConfig);
            $this->_db[$db]->query('SET names UTF8');
            $this->_db[$db]->query("SET lc_time_names = '".trlVps('en_US')."'");


            if (Zend_Registry::get('config')->debug->querylog) {
                $profiler = new Vps_Db_Profiler(true);
                $this->_db[$db]->setProfiler($profiler);
            } else if (Zend_Registry::get('config')->debug->benchmark || Zend_Registry::get('config')->debug->benchmarkLog) {
                $profiler = new Vps_Db_Profiler_Count(true);
                $this->_db[$db]->setProfiler($profiler);
            }
        }
        return $this->_db[$db];
    }
}
