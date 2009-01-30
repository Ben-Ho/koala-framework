<?php
class Vpc_Composite_ImagesEnlarge_Root extends Vps_Component_NoCategoriesRoot
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['page']['model'] = new Vps_Model_FnF(array('data'=>array(
            array('id'=>2200, 'pos'=>1, 'visible'=>true, 'name'=>'Foo', 'filename' => 'foo',
                  'parent_id'=>null, 'component'=>'images', 'is_home'=>false, 'category' =>'main', 'hide'=>false),

        )));
        $ret['generators']['page']['component'] = array(
            'images' => 'Vpc_Composite_ImagesEnlarge_TestComponent',
        );

        unset($ret['generators']['title']);
        unset($ret['generators']['box']);
        return $ret;
    }
}
