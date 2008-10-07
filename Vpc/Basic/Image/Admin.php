<?php
class Vpc_Basic_Image_Admin extends Vpc_Admin
{
    protected function _deleteCacheForRow($row)
    {
        if ($row instanceof Vpc_Basic_Image_Row &&
            Vpc_Abstract::hasSetting($this->_class, 'useParentImage') && 
            Vpc_Abstract::getSetting($this->_class, 'useParentImage')
        ) {
            Vps_Component_Cache::getInstance()->remove(
                Vps_Component_Data_Root::getInstance()->getComponentByDbId(
                    $row->component_id, array('ignoreVisible' => true)
                )->getChildComponents(array('componentClass'=>$this->_class))
            );
        } else {
            parent::_deleteCacheForRow($row);
        }
    }
    
    public function setup()
    {
        $fields['filename'] = 'varchar(255) DEFAULT NULL';
        $fields['width'] = 'int(11) DEFAULT NULL';
        $fields['height'] = 'int(11) DEFAULT NULL';
        $fields['scale'] = 'varchar(255) DEFAULT NULL';
        $fields['enlarge'] = 'tinyint(3) DEFAULT 0';
        $fields['vps_upload_id'] = 'int(11) DEFAULT NULL';
        $this->createFormTable('vpc_basic_image', $fields);
    }
}
