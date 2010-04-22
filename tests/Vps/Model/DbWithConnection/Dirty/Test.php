<?php
/**
 * @group Model
 * @group Model_Db
 * @group Model_DbWithConnection
 * @group Model_Db_Dirty
 */
class Vps_Model_DbWithConnection_Test extends PHPUnit_Extensions_OutputTestCase
{
    private $_tableName;
    public function setUp()
    {
        $this->_tableName = 'test'.uniqid();
        $sql = "CREATE TABLE $this->_tableName (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `test1` VARCHAR( 200 ) character set utf8 NOT NULL,
            `test2` VARCHAR( 200 ) character set utf8 NOT NULL
        ) ENGINE = INNODB DEFAULT CHARSET=utf8";
        Vps_Registry::get('db')->query($sql);
        $m = new Vps_Model_Db(array(
            'table' => $this->_tableName
        ));
        $r = $m->createRow();
        $r->test1 = 'foo';
        $r->test2 = 'bar';
        $r->save();
    }

    public function tearDown()
    {
        Vps_Registry::get('db')->query("DROP TABLE {$this->_tableName}");
    }

    public function testDontSaveNotDirtyRow()
    {
        // warum auch immer das nicht automatisch geht...
        require_once dirname(__FILE__).'/Row.php';

        Vps_Model_DbWithConnection_Row::resetMock();

        $table = new Vps_Db_Table(array(
            'name' => $this->_tableName,
            'rowClass' => 'Vps_Model_DbWithConnection_Row'
        ));
        $model = new Vps_Model_Db(array(
            'table' => $table
        ));

        $row = $model->getRow(1);
        $row->save();

        $this->assertEquals(0, Vps_Model_DbWithConnection_Row::$saveCount);

        $row = $model->getRow(1);
        $row->test1 = 'foo';
        $row->save();

        $this->assertEquals(0, Vps_Model_DbWithConnection_Row::$saveCount);
    }

    public function testSaveNewRowNotDirty()
    {
        // warum auch immer das nicht automatisch geht...
        require_once dirname(__FILE__).'/Row.php';

        Vps_Model_DbWithConnection_Row::resetMock();

        $table = new Vps_Db_Table(array(
            'name' => $this->_tableName,
            'rowClass' => 'Vps_Model_DbWithConnection_Row'
        ));
        $model = new Vps_Model_Db(array(
            'table' => $table
        ));

        $row = $model->createRow();
        $row->save();
        $this->assertEquals(1, Vps_Model_DbWithConnection_Row::$saveCount);
    }

    public function testSaveDirtyRow()
    {
        // warum auch immer das nicht automatisch geht...
        require_once dirname(__FILE__).'/Row.php';

        Vps_Model_DbWithConnection_Row::resetMock();

        $table = new Vps_Db_Table(array(
            'name' => $this->_tableName,
            'rowClass' => 'Vps_Model_DbWithConnection_Row'
        ));
        $model = new Vps_Model_Db(array(
            'table' => $table
        ));

        $row = $model->getRow(1);
        $row->test1 = 'blubb';
        $row->save();

        $this->assertEquals(1, Vps_Model_DbWithConnection_Row::$saveCount);

        Vps_Model_DbWithConnection_Row::resetMock();
        $row = $model->createRow();
        $row->test1 = 'xx';
        $row->test2 = 'yy';
        $row->save();

        $this->assertEquals(1, Vps_Model_DbWithConnection_Row::$saveCount);
    }
}
