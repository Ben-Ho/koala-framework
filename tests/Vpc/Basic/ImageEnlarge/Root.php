<?php
class Vpc_Basic_ImageEnlarge_Root extends Vpc_Root_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['page']['model'] = new Vps_Model_FnF(array('data'=>array(
            array('id'=>1800, 'pos'=>1, 'visible'=>true, 'name'=>'Foo', 'filename' => 'foo',
                  'parent_id'=>null, 'component'=>'imageWithoutSmall', 'is_home'=>false, 'type'=>'main', 'hide'=>false),
            array('id'=>1801, 'pos'=>1, 'visible'=>true, 'name'=>'Foo', 'filename' => 'foo',
                  'parent_id'=>null, 'component'=>'image', 'is_home'=>false, 'type'=>'main', 'hide'=>false),
            array('id'=>1802, 'pos'=>1, 'visible'=>true, 'name'=>'Foo', 'filename' => 'foo',
                  'parent_id'=>null, 'component'=>'image', 'is_home'=>false, 'type'=>'main', 'hide'=>false),
            array('id'=>1803, 'pos'=>1, 'visible'=>true, 'name'=>'Foo', 'filename' => 'foo',
                  'parent_id'=>null, 'component'=>'imageWithOriginal', 'is_home'=>false, 'type'=>'main', 'hide'=>false),
        )));
        $ret['generators']['page']['component'] = array(
            'image' => 'Vpc_Basic_ImageEnlarge_TestComponent',
            'imageWithoutSmall' => 'Vpc_Basic_ImageEnlarge_WithoutSmallImageComponent',
            'imageWithOriginal' => 'Vpc_Basic_ImageEnlarge_OriginalImageComponent',
        );

        unset($ret['generators']['title']);
        unset($ret['generators']['box']);
        return $ret;
    }
}
