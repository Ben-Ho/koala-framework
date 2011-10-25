<?php
class Kwf_Component_Cache_Fnf_MetaComponentModel extends Kwf_Component_Cache_Mysql_MetaComponentModel
{
    public function __construct(array $config = array())
    {
        $config['proxyModel'] = new Kwf_Model_FnF(array(
            'primaryKey' => 'fakeId',
            'columns' => array('fakeId', 'db_id', 'component_class', 'target_db_id', 'target_component_class', 'meta_class'),
            'uniqueColumns' => array('db_id', 'component_class', 'target_component_class', 'target_db_id')
        ));
        parent::__construct($config);
    }
}
