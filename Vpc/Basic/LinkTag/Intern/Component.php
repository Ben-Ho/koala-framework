<?php
/**
 * @package Vpc
 * @subpackage Basic
 */
class Vpc_Basic_LinkTag_Intern_Component extends Vpc_Basic_LinkTag_Abstract_Component
{
    public static function getSettings()
    {
        $ret = array_merge(parent::getSettings(), array(
            'dataClass' => 'Vpc_Basic_LinkTag_Intern_Data',
            'ownModel'     => 'Vpc_Basic_LinkTag_Intern_Model',
            'componentName' => trlVps('Link.Intern'),
        ));
        $ret['assetsAdmin']['files'][] = 'vps/Vpc/Basic/LinkTag/Intern/LinkField.js';
        return $ret;
    }
    public function getCacheVars()
    {
        $ret = parent::getCacheVars();
        $linkedData = $this->getData()->getLinkedData();
        if ($linkedData && isset($linkedData->row) && $linkedData->row) {
            $ret[] = array(
                'model' => 'Vps_Component_Model',
                'id' => $linkedData->row->id
            );
            $ret[] = array(
                'model' => 'Vpc_Root_Category_GeneratorModel',
                'id' => $linkedData->row->id
            );
            if ($linkedData instanceof Vpc_Basic_LinkTag_FirstChildPage_Data) {
                $childData = $linkedData->_getFirstChildPage();
                $ret = array_merge($ret, $childData->getComponent()->getCacheVars());
            }
        }
        return $ret;
    }
}
