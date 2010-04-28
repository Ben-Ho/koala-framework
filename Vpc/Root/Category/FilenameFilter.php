<?php
class Vpc_Root_Category_FilenameFilter extends Vps_Filter_Row_Abstract
{
    public function skipFilter($row)
    {
        if ($row->custom_filename) return true;
        return parent::skipFilter($row);
    }

    public function filter($row)
    {
        $value = Vps_Filter::filterStatic($row->name, 'Ascii');

        $componentId = $this->_getComponentId($row);
        if (!$componentId && isset($row->parent_id)) {
            $parent = Vps_Component_Data_Root::getInstance()
                ->getComponentById($row->parent_id, array('ignoreVisible' => true));
        } else {
            $parent = Vps_Component_Data_Root::getInstance()
                ->getComponentById($componentId, array('ignoreVisible' => true))
                ->parent;
        }
        $values = array();
        foreach ($parent->getChildPages(array('ignoreVisible' => true)) as $c) {
            if ($c->componentId == $componentId) continue;
            $values[] = $c->filename;
        }

        $x = 0;
        $unique = $value;
        if (!$unique) $unique = 1;
        while (in_array($unique, $values)) {
            if ($value) {
                $unique = $value . '_' . ++$x;
            } else {
                $unique = ++$x;
            }
        }

        return (string)$unique;
    }

    protected function _getComponentId($row)
    {
        return $row->id;
    }
}
