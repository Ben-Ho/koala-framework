<?php
class Vpc_Paragraphs_Admin extends Vpc_Admin
{
    private function _componentNameToArray($name, $component, &$componentList)
    {
        $names = explode('.', $name, 2);
        if (count($names) > 1) {
            $this->_componentNameToArray($names[1], $component, $componentList[$names[0]]);
        } else {
            $componentList[$name] = $component;
        }
    }

    public function getExtConfig()
    {
        $componentList = array();
        foreach ($this->_getComponents() as $component) {
            if (!Vpc_Abstract::hasSetting($component, 'componentName')) continue;
            $name = Vpc_Abstract::getSetting($component, 'componentName');
            $icon = Vpc_Abstract::getSetting($component, 'componentIcon');
            if ($icon) {
                $icon = $icon->__toString();
            }
            if ($name) {
                $this->_componentNameToArray($name, $component, $componentList);
                $componentIcons[$component] = $icon;
            }
        }

        return array_merge(parent::getExtConfig(), array(
            'xtype'=>'vpc.paragraphs',
            'components' => $componentList,
            'componentIcons' => $componentIcons
        ));
    }

    protected function _getComponents()
    {
        return Vpc_Abstract::getSetting($this->_class, 'childComponentClasses');
    }

    public function setup()
    {
        $tablename = 'vpc_paragraphs';
        if (!$this->_tableExists($tablename)) {
          $this->_db->query("CREATE TABLE `$tablename` (
                  `id` int(10) unsigned NOT NULL auto_increment,
                  `component_id` varchar(255) NOT NULL,
                  `component` varchar(255) NOT NULL,
                  `pos` smallint NOT NULL,
                  `visible` tinyint(4) NOT NULL
                   PRIMARY KEY  (`id`)
                     ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        }
    }

    public function delete($componentId)
    {
        foreach ($this->_getRows($componentId) as $row) {
            $row->delete();
        }
    }
}
