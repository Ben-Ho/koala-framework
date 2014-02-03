<?php
class Kwf_Component_Generator_Categories_PagesModel extends Kwf_Model_FnF
{
    protected $_data = array(
        array('id'=>1, 'pos'=>1, 'visible'=>true, 'name'=>'Home', 'filename' => 'home', 'custom_filename' => false,
                'parent_id'=>'root-main', 'component'=>'empty', 'is_home'=>true, 'hide'=>false, 'parent_subroot_id' => 'root'),
        array('id'=>2, 'pos'=>1, 'visible'=>true, 'name'=>'Foo', 'filename' => 'foo', 'custom_filename' => false,
                'parent_id'=>1, 'component'=>'empty', 'is_home'=>false, 'hide'=>false, 'parent_subroot_id' => 'root'),
        array('id'=>4, 'pos'=>2, 'visible'=>true, 'name'=>'Foo3', 'filename' => 'foo3', 'custom_filename' => false,
                'parent_id'=>'root-bottom', 'component'=>'empty', 'is_home'=>false, 'hide'=>false, 'parent_subroot_id' => 'root'),
    );
}
