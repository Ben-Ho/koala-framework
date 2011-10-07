<?php
class Kwf_Model_DbWithConnection_SiblingRelationExpr_MasterDbModel extends Kwf_Model_Db
{
    protected $_tableName;
    public function __construct()
    {
        $this->_tableName = 'master'.uniqid();
        $config['table'] = $this->_tableName;
        Kwf_Registry::get('db')->query("CREATE TABLE {$this->_tableName} (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY
        ) ENGINE = INNODB");
        Kwf_Registry::get('db')->query("INSERT INTO {$this->_tableName}
                        (id) VALUES ('1')");
        Kwf_Registry::get('db')->query("INSERT INTO {$this->_tableName}
                        (id) VALUES ('2')");

        parent::__construct($config);
    }

    public function dropTable()
    {
        Kwf_Registry::get('db')->query("DROP TABLE IF EXISTS {$this->_tableName}");
    }
}