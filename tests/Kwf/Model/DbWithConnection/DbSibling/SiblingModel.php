<?php
class Kwf_Model_DbWithConnection_DbSibling_SiblingModel extends Kwf_Model_Db
{
    protected $_referenceMap = array(
        'Master' => array(
            'column' => 'master_id',
            'refModelClass' => 'Kwf_Model_DbWithConnection_DbSibling_MasterModel'
        )
    );
    private $_tableName;

    public function __construct($config = array())
    {
        $this->_tableName = 'sibling'.uniqid();
        $config['table'] = $this->_tableName;
        Kwf_Registry::get('db')->query("CREATE TABLE {$this->_tableName} (
                `master_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                `baz` VARCHAR( 200 ) NOT NULL
            ) ENGINE = INNODB");
        Kwf_Registry::get('db')->query("INSERT INTO {$this->_tableName}
                        (master_id, baz) VALUES ('1', 'aha')");

        parent::__construct($config);
    }

    public function dropTable()
    {
        Kwf_Registry::get('db')->query("DROP TABLE IF EXISTS {$this->_tableName}");
    }

    public function clearRows()
    {
        $this->_rows = array();
    }

}
