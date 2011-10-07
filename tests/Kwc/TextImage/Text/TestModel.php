<?php
class Kwc_TextImage_Text_TestModel extends Kwc_Basic_Text_Model
{
    public function __construct($config = array())
    {
        $this->_dependentModels['ChildComponents'] = 'Kwc_TextImage_Text_ChildComponentsModel';

        $config['proxyModel'] = new Kwf_Model_FnF(array(
                'primaryKey' => 'component_id',
                'data'=> array(
                    array('component_id'=>'root_textImage1-text', 'content'=>'<p>foo</p>', 'data'=>'')
                )
            ));
        parent::__construct($config);
    }
}
