<?php
abstract class Vpc_Basic_LinkTag_Abstract_Component extends Vpc_Abstract
{
    public static function getSettings()
    {
        return array_merge(parent::getSettings(), array(
            'componentIcon' => new Vps_Asset('page_link')
        ));
    }

    public function getCacheVars()
    {
        $ret = parent::getCacheVars();
        $parent = $this->getData()->parent;
        if ($parent->getComponent() instanceof Vpc_Basic_LinkTag_Component) {
            $model = $parent->getComponent()->getModel();
            $row = $model->getRow($parent->dbId);
            $ret[] = array(
                'model' => $model,
                'id' => $row->component_id
            );
            $ret[] = array(
                'model' => $model,
                'id' => $row->component_id,
                'callback' => true
            );
        }
        return $ret;
    }

    public function onCacheCallback($row)
    {
        if ($this->getData()->parent->isPage) {
            foreach (Vpc_Abstract::getComponentClasses() as $componentClass) {
                if (is_instance_of($componentClass, 'Vpc_Menu_Abstract')) {
                    Vps_Component_Cache::getInstance()->cleanComponentClass($componentClass);
                }
            }
        }
    }

}
