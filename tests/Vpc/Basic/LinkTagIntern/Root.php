<?php
class Vpc_Basic_LinkTagIntern_Root extends Vpc_Root_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['page']['model'] = new Vps_Model_FnF(array('data'=>array(
            array('id'=>1300, 'pos'=>1, 'visible'=>true, 'name'=>'Foo', 'filename' => 'foo',
                  'parent_id'=>null, 'component'=>'link', 'is_home'=>false, 'type'=>'main', 'hide'=>false),
            array('id'=>1301, 'pos'=>1, 'visible'=>true, 'name'=>'Foo', 'filename' => 'foo',
                  'parent_id'=>null, 'component'=>'link', 'is_home'=>false, 'type'=>'main', 'hide'=>false),
            array('id'=>1310, 'pos'=>2, 'visible'=>true, 'name'=>'Bar', 'filename' => 'bar',
                  'parent_id'=>null, 'component'=>'empty', 'is_home'=>false, 'type'=>'main', 'hide'=>false)
        )));
        $ret['generators']['page']['component'] = array(
            'link' => 'Vpc_Basic_LinkTagIntern_TestComponent',
            'empty' => 'Vpc_Basic_Empty_Component'
        );

        unset($ret['generators']['title']);
        unset($ret['generators']['box']);
        return $ret;
    }
}
