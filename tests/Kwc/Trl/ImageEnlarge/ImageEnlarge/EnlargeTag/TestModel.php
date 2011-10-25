<?php
class Kwc_Trl_ImageEnlarge_ImageEnlarge_EnlargeTag_TestModel extends Kwc_Abstract_Image_Model
{
    public function __construct()
    {
        $config['proxyModel'] = new Kwf_Model_FnFFile(array(
            'primaryKey' => 'component_id',
            'uniqueIdentifier' => get_class($this).'-Proxy'
        ));
        $this->_referenceMap['Image']['refModelClass'] = 'Kwc_Trl_ImageEnlarge_UploadsModel';
        parent::__construct($config);
    }


    protected function _init()
    {
        parent::_init();
        $this->_siblingModels = array();
    }
}
