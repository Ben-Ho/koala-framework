<?php
/**
 * @group Update_Action
 */
class Vps_Update_Action_Db_DropFieldTest extends PHPUnit_Framework_TestCase
{
    public function testRenameField()
    {
        $model = new Vps_Update_Action_Db_TestModel();

        $rows = $model->getRow('foo')->getChildRows('Fields');
        $this->assertEquals(2, count($rows));

        $a = new Vps_Update_Action_Db_DropField();
        $a->model = $model;
        $a->table = 'foo';
        $a->field = 'bar';
        $a->update();

        $rows = $model->getRow('foo')->getChildRows('Fields');
        $this->assertEquals(1, count($rows));

        $row = $model->getRow('foo')->getChildRows('Fields', $model->select()->whereId('bar'))->current();
        $this->assertNull($row);
    }
}
