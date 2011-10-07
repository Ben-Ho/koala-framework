<?php
class Kwf_Acl_Resource_Component_MenuUrl extends Kwf_Acl_Resource_MenuUrl
    implements Kwf_Acl_Resource_Component_Interface
{
    protected $_component;

    public function __construct(Kwf_Component_Data $component, $menuConfig = null, $menuUrl = null)
    {
        $this->_component = $component;
        if (!$menuConfig) {
            $name = Kwc_Abstract::getSetting($component->componentClass, 'componentName');
            $icon = Kwc_Abstract::getSetting($component->componentClass, 'componentIcon');
            if (strpos($name, '.') !== false) $name = substr($name, strrpos($name, '.') + 1);
            $menuConfig = array('text'=>trlKwf('Edit {0}', $name), 'icon'=>$icon);
        }
        if (!$menuUrl) {
            $menuUrl = Kwc_Admin::getInstance($component->componentClass)
                ->getControllerUrl() . '?componentId=' . $component->dbId;
        }
        parent::__construct('kwc_'.$component->dbId, $menuConfig, $menuUrl);
    }

    public function getComponent()
    {
        return $this->_component;
    }
}
