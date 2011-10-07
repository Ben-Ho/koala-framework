<?php
class Kwf_Component_Generator_Subroot_Category extends Kwc_Root_Category_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['page']['model'] = new Kwf_Model_FnF(array('data'=>array(
            array('id'=>1, 'pos'=>1, 'visible'=>true, 'name'=>'Home', 'filename' => 'home',
                  'parent_id'=>'root-at-main', 'component'=>'empty', 'is_home'=>true, 'category'=>'main', 'domain' => 'at', 'hide'=>false),
            array('id'=>2, 'pos'=>1, 'visible'=>true, 'name'=>'Foo', 'filename' => 'foo',
                  'parent_id'=>1, 'component'=>'empty', 'is_home'=>false, 'category'=>'main', 'domain' => 'at', 'hide'=>false),
            array('id'=>4, 'pos'=>2, 'visible'=>true, 'name'=>'Foo3', 'filename' => 'foo3',
                  'parent_id'=>'root-at-bottom', 'component'=>'image', 'is_home'=>false, 'category'=>'bottom', 'domain' => 'at', 'hide'=>false),
            array('id'=>5, 'pos'=>1, 'visible'=>true, 'name'=>'Home', 'filename' => 'home',
                  'parent_id'=>'root-ch-main', 'component'=>'image', 'is_home'=>true, 'category'=>'main', 'domain' => 'ch', 'hide'=>false),
            array('id'=>6, 'pos'=>1, 'visible'=>true, 'name'=>'Foo', 'filename' => 'foo',
                  'parent_id'=>5, 'component'=>'empty_ch', 'is_home'=>false, 'category'=>'main', 'domain' => 'ch', 'hide'=>false),
            array('id'=>7, 'pos'=>2, 'visible'=>true, 'name'=>'Foo3', 'filename' => 'foo3',
                  'parent_id'=>'root-ch-bottom', 'component'=>'empty_ch', 'is_home'=>false, 'category'=>'bottom', 'domain' => 'ch', 'hide'=>false),
            )));
        $ret['generators']['page']['component'] = array(
            'image' => 'Kwc_Basic_Image_Component',
            'empty' => 'Kwc_Basic_Empty_Component',
            'empty_ch' => 'Kwc_Basic_Link_Component',
        );
        return $ret;
    }
}
?>