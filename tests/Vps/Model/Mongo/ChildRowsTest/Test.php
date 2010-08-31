<?php
/**
 * @group Mongo
 * @group Mongo_ChildRows
 * @group slow
 */
class Vps_Model_Mongo_ChildRowsTest_Test extends PHPUnit_Framework_TestCase
{
    private $_model;
    public function setUp()
    {
        Vps_Model_Abstract::clearInstances();

        $this->_model = Vps_Model_Abstract::getInstance('Vps_Model_Mongo_ChildRowsTest_MongoModel');
        $this->_model->getCollection()->insert(
            array('id'=>1, 'a'=>'a', 'foo'=>array(array('x'=>1), array('x'=>2))) //TODO id sollte nicht nötig sein
        , array('safe'=>true));
    }

    protected function tearDown()
    {
        if (isset($this->_model)) $this->_model->cleanUp();
        Vps_Model_Abstract::clearInstances();
    }

    public function testRead()
    {
        $row = $this->_model->getRow(1);
        $rows = $row->getChildRows('Foo');
        $this->assertEquals(2, count($rows));
        $this->assertEquals(1, $rows->current()->x);
    }

    public function testCreateChild()
    {
        $row = $this->_model->getRow(1);
        $crow = $row->createChildRow('Foo');
        $crow->x = 3;
        $row->save();
        $this->assertEquals(3, count($row->getChildRows('Foo')));

        $row = $this->_model->getCollection()->findOne(array('a'=>'a'));
        $this->assertEquals(3, count($row['foo']));
        $this->assertEquals(3, $row['foo'][2]['x']);
    }

    public function testDeleteChild()
    {
        $this->markTestIncomplete();
        $row = $this->_model->getRow(1);
        $crows = $row->getChildRows('Foo');
        $crows->current()->delete();
        $row->save();
        $this->assertEquals(1, count($row->getChildRows('Foo')));

        $row = $this->_model->getCollection()->findOne(array('a'=>'a'));
        $this->assertEquals(1, count($row['foo']));
    }

    public function testUpdateChild()
    {
        $this->markTestIncomplete();
    }

}
