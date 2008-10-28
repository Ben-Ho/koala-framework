<?php
class Vpc_Basic_DownloadTag_Root extends Vpc_Root_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['page']['model'] = new Vps_Model_FnF(array('data'=>array(
            array('id'=>1700, 'pos'=>1, 'visible'=>true, 'name'=>'Foo', 'filename' => 'foo',
                  'parent_id'=>null, 'component'=>'downloadTag', 'is_home'=>false, 'type'=>'main', 'hide'=>false),
            array('id'=>1701, 'pos'=>1, 'visible'=>true, 'name'=>'Foo', 'filename' => 'foo',
                  'parent_id'=>null, 'component'=>'downloadTag', 'is_home'=>false, 'type'=>'main', 'hide'=>false),
            array('id'=>1702, 'pos'=>1, 'visible'=>true, 'name'=>'Foo', 'filename' => 'foo',
                  'parent_id'=>null, 'component'=>'downloadTag', 'is_home'=>false, 'type'=>'main', 'hide'=>false),
        )));
        $ret['generators']['page']['component'] = array(
            'downloadTag' => 'Vpc_Basic_DownloadTag_TestComponent',
        );

        unset($ret['generators']['title']);
        unset($ret['generators']['box']);
        return $ret;
    }
}
