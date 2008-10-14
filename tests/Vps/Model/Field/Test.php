<?php
class Vps_Model_Field_Test extends PHPUnit_Framework_TestCase
{
    public function testFnFField()
    {
        $model = new Vps_Model_FnF(array(
            'columns' => array('id', 'foo', 'data'),
            'data'=>array(array('id'=>1, 'foo'=>'bar', 'data'=>serialize(array('blub'=>'blub')))),
            'siblingModels' => array(new Vps_Model_Field(array('fieldName'=>'data')))
        ));

        $row = $model->getRow(1);
        $this->assertEquals($row->foo, 'bar');
        $this->assertEquals($row->blub, 'blub');
        $row->blub1 = 'blub1';
        $row->save();

        $this->assertEquals($model->getData(), array(array('id'=>1, 'foo'=>'bar', 'data'=>serialize(array('blub'=>'blub', 'blub1'=>'blub1')))));
        $row = $model->getRow(1);
        $this->assertEquals($row->blub1, 'blub1');

        $row = $model->createRow();
        $row->id = 2;
        $row->foo = 'newFoo';
        $row->blub = 'newBlub';
        $row->save();
        $this->assertEquals($model->getData(), array(
            array('id'=>1, 'foo'=>'bar', 'data'=>serialize(array('blub'=>'blub', 'blub1'=>'blub1'))),
            array('id'=>2, 'foo'=>'newFoo', 'data'=>serialize(array('blub'=>'newBlub'))),
        ));
    }

    public function testFnFFieldField()
    {
        $model = new Vps_Model_FnF(array(
            'columns' => array('id', 'foo', 'data'),
            'data'=>array(array('id'=>1, 'foo'=>'bar', 'data'=>serialize(array('blub'=>'blub',
                        'data'=>serialize(array('blub1'=>'blub1')))))),
            'siblingModels' => array(new Vps_Model_Field(array(
                'fieldName'=>'data',
                'columns' => array('blub', 'data'),
                'siblingModels' => array(new Vps_Model_Field(array(
                    'fieldName' => 'data',
                    'columns' => array('blub1', 'blub2')
                )))
            )))
        ));

        $this->assertTrue($model->hasColumn('foo'));
        $this->assertTrue($model->hasColumn('blub'));
        $this->assertTrue($model->hasColumn('blub1'));
        $this->assertTrue($model->hasColumn('blub2'));

        $row = $model->getRow(1);
        $this->assertEquals($row->foo, 'bar');
        $this->assertEquals($row->blub, 'blub');
        $this->assertEquals($row->blub1, 'blub1');
        $row->blub2 = 'blub2';
        $row->save();

        $this->assertEquals($model->getData(), array(array('id'=>1, 'foo'=>'bar', 'data'=>serialize(array('blub'=>'blub',
                'data'=>serialize(array('blub1'=>'blub1', 'blub2'=>'blub2')))))));
        $row = $model->getRow(1);
        $this->assertEquals($row->blub2, 'blub2');
    }

    public function testDataIsEmpty()
    {
        $model = new Vps_Model_FnF(array(
            'columns' => array('id', 'foo', 'data'),
            'data'=>array(array('id'=>1, 'foo'=>'bar', 'data'=>'')),
            'siblingModels' => array(new Vps_Model_Field(array('fieldName'=>'data')))
        ));
        $row = $model->getRow(1);
        $row->blub = 1;
        $row->save();
        $this->assertEquals($model->getData(), array(
            array('id'=>1, 'foo'=>'bar', 'data'=>serialize(array('blub'=>1)))
        ));
    }

    public function testDataIsLegacy()
    {
        $model = new Vps_Model_FnF(array(
            'columns' => array('id', 'foo', 'data'),
            'data'=>array(array('id'=>1, 'foo'=>'bar', 'data'=>'vpsSerialized'.serialize(array('blub'=>'blub')))),
            'siblingModels' => array(new Vps_Model_Field(array('fieldName'=>'data')))
        ));
        $row = $model->getRow(1);
        $row->blub = 1;
        $row->save();
        $this->assertEquals($model->getData(), array(
            array('id'=>1, 'foo'=>'bar', 'data'=>serialize(array('blub'=>1)))
        ));
    }
}
