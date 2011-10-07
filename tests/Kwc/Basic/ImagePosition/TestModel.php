<?php
class Kwc_Basic_ImagePosition_TestModel extends Kwf_Component_FieldModel
{
    public function __construct($config = array())
    {
        $config['proxyModel'] = new Kwf_Model_FnF(array(
                'columns' => array('component_id', 'data'),
                'primaryKey' => 'component_id',
                'data'=> array(
                    array('component_id'=>1900, 'data'=>json_encode(array('image_position'=>'right'))),
                )
            ));
        parent::__construct($config);
    }
}
