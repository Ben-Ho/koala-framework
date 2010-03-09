<?php
class Vpc_Box_InheritContent_Component extends Vpc_Abstract
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['child'] = array(
            'class' => 'Vps_Component_Generator_Static',
            'component' => false
        );
        $ret['editComponents'] = array('child');

        //TODO: viewcache nicht deaktiveren
        //cache löschen muss dazu korrekt eingebaut werden
        $ret['viewCache'] = false;
        return $ret;
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        $ret['child'] = $this->getContentChild();
        return $ret;
    }

    public function getExportData()
    {
        return $this->getContentChild()->getComponent()->getExportData();
    }

    public function getContentChild()
    {
        $page = $this->getData();
        do {
            while ($page && !$page->inherits) {
                $page = $page->parent;
                if ($page instanceof Vps_Component_Data_Root) break;
            }
            $ic = $page->getChildComponent('-'.$this->getData()->id);
            if (!$ic) {
                return null;
            }
            $c = $ic->getChildComponent(array('generator' => 'child'));
            if ($page instanceof Vps_Component_Data_Root) break;
            $page = $page->parent;
        } while(!$c->hasContent());
        return $c;
    }

    public function hasContent()
    {
        return true;
    }
}
