<?php
class Kwf_Component_Cache_Fnf_MetaRowModel extends Kwf_Component_Cache_Mysql_MetaRowModel
{
    public function __construct(array $config = array())
    {
        $config['proxyModel'] = new Kwf_Model_FnF(array(
            'primaryKey' => 'fakeId',
            'columns' => array('fakeId', 'model', 'column', 'value', 'component_id', 'component_class', 'meta_class'),
            'uniqueColumns' => array('model', 'column', 'value', 'component_id'),
            'default' => array('callback' => false)
        ));
        parent::__construct($config);
    }
}
