<?php
/**
 * @group Model
 * @group Model_Db_Sibling_Proxy
 * @group Model_Db
 * @group Model_DbWithConnection
 */
class Kwf_Model_DbWithConnection_DbSiblingProxy_Test extends Kwf_Test_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->_model = new Kwf_Model_DbWithConnection_DbSiblingProxy_ProxyModel();
    }

    public function tearDown()
    {
        $this->_model->dropTable();
        parent::tearDown();
    }

    public function testIt()
    {
        $m = $this->_model;

        $r = $m->getRow(1);
        $this->assertEquals('aaabbbccc', $r->foo);
        $this->assertEquals('abcd', $r->bar);
        $this->assertEquals('aha', $r->baz);

        $r = $m->getRow(2);
        $this->assertEquals('bam', $r->foo);
        $this->assertEquals('bum', $r->bar);
        $this->assertEquals(null, $r->baz);

        $r = $m->getRow($m->select()->whereEquals('baz', 'aha'));
        $this->assertNotNull($r);
        $this->assertEquals(1, $r->id);

        $r = $m->getRow($m->select()->whereNull('baz'));
        $this->assertNotNull($r);
        $this->assertEquals(2, $r->id);

        $r = $m->getRows($m->select()->order('baz'));
        $this->assertEquals(2, count($r));

        $r = $m->createRow();
        $r->foo = 'xxy';
        $r->baz = 'xxz';
        $r->save();

        $tableName = Kwf_Model_Abstract::getInstance('Kwf_Model_DbWithConnection_DbSiblingProxy_DbModel')
                        ->getTable()->info(Zend_Db_Table_Abstract::NAME);
        $m = new Kwf_Model_Db(array('table'=>$tableName));
        $r = $m->getRow(3);
        $this->assertEquals('xxy', $r->foo);
        $this->assertEquals(null, $r->bar);

        $tableName = Kwf_Model_Abstract::getInstance('Kwf_Model_DbWithConnection_DbSiblingProxy_SiblingModel')
                        ->getTable()->info(Zend_Db_Table_Abstract::NAME);
        $m = new Kwf_Model_Db(array('table'=>$tableName));
        $r = $m->getRow(3);
        $this->assertEquals('xxz', $r->baz);

    }

    public function testDuplicate()
    {
        $m = $this->_model;

        $r = $m->getRow(1)->duplicate();
        $this->assertEquals('aaabbbccc', $r->foo);
        $this->assertEquals('abcd', $r->bar);
        $this->assertEquals('aha', $r->baz);
    }

    public function testDirty()
    {
        $row = $this->_model->getRow(1);
        $this->assertEquals($row->getDirtyColumns(), array());
        $this->assertEquals($row->isDirty(), false);
        $this->assertEquals($row->getCleanValue('baz'), 'aha');
        $row->baz = 'foo1';
        $this->assertEquals($row->getDirtyColumns(), array('baz'));
        $this->assertEquals($row->isDirty(), true);
        $this->assertEquals($row->getCleanValue('baz'), 'aha');
    }
}
