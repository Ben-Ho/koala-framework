<?php
class Vpc_Trl_News_News_TestModel extends Vps_Model_FnF
{
    public function __construct()
    {
        $config = array(
            'data'=> array(
                array('id'=>'1', 'component_id'=>'root-master_test', 'visible'=>true, 'title'=>'lipsum', 'teaser'=>'blablub'),
                array('id'=>'2', 'component_id'=>'root-master_test', 'visible'=>true, 'title'=>'lipsum2', 'teaser'=>'blablub2'),
            )
        );
        parent::__construct($config);
    }
}
