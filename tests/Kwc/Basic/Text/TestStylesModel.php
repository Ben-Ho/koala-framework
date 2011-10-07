<?php
class Kwc_Basic_Text_TestStylesModel extends Kwc_Basic_Text_StylesModel
{
    public function __construct($config = array())
    {
        $config['proxyModel'] = new Kwf_Model_FnF(array(
                'uniqueIdentifier' => 'Kwc_Basic_Text_TestStylesModel_Proxy',
                'columns' => array('id', 'pos', 'name', 'tag', 'ownStyles', 'styles'),
                'data'=> array(
                    array('id'=>1, 'pos'=>1, 'name'=>'Test1', 'tag'=>'h1', 'ownStyles'=>'', 'styles'=>json_encode(array('font_weight'=>'bold', 'font_size'=>'10', 'text_align'=>'center'))),
                    array('id'=>2, 'pos'=>2, 'name'=>'Test2', 'tag'=>'p', 'ownStyles'=>'', 'styles'=>json_encode(array('font_size'=>'10', 'color'=>'ff0000'))),
                    array('id'=>3, 'pos'=>3, 'name'=>'Test3', 'tag'=>'span', 'ownStyles'=>'', 'styles'=>json_encode(array('font_size'=>'8', 'color'=>'00ff00'))),
                )
            ));
        parent::__construct($config);
    }
    public static function getMasterStyles()
    {
        return array();
    }
}
