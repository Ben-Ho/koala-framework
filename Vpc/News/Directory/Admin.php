<?php
class Vpc_News_Directory_Admin extends Vpc_Directories_Item_Directory_Admin
{
    protected function _getContentClass()
    {
        $detail = Vpc_Abstract::getChildComponentClass($this->_class, 'detail');
        return Vpc_Abstract::getChildComponentClass($detail, 'child', 'content');
    }

    public function getExtConfig()
    {
        $ret = parent::getExtConfig();
        $ret['items']['idTemplate'] = 'news_{0}-content';
        return $ret;
    }

    protected function _getPluginParentComponents()
    {
        $detail = Vpc_Abstract::getChildComponentClass($this->_class, 'detail');
        return array($detail, $this->_class);
    }

    public function addResources(Vps_Acl $acl)
    {
        parent::addResources($acl);
        $this->_addResourcesBySameClass($acl);
    }
}
