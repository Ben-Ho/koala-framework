<?php
class Vpc_Box_SwitchLanguage_Component extends Vpc_Abstract
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['cssClass'] = 'webStandard';
        $ret['separator'] = ' / ';
        $ret['showCurrent'] = true;
        return $ret;
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        $ret['separator'] = $this->_getSetting('separator');
        $languages = Vps_Component_Data_Root::getInstance()
            ->getComponentsByClass('Vpc_Root_LanguageRoot_Language_Component');
        $languages = array_merge($languages, Vps_Component_Data_Root::getInstance()
            ->getComponentsByClass('Vpc_Root_TrlRoot_Master_Component'));
        $languages = array_merge($languages, Vps_Component_Data_Root::getInstance()
            ->getComponentsByClass('Vpc_Root_TrlRoot_Chained_Component'));
        $ret['languages'] = array();
        foreach ($languages as $l) {
            if (!$this->_getSetting('showCurrent')) {
                if ($this->getData()->getLanguageData()->componentId == $l->componentId) {
                    continue;
                }
            }
            $masterPage = $this->getData()->getPage();
            if (isset($masterPage->chained)) {
                $masterPage = $masterPage->chained; //TODO: nicht sauber
            }
            $page = null;
            if ($masterPage) {
                if (is_instance_of($l->componentClass, 'Vpc_Root_TrlRoot_Chained_Component')) {
                    $page = Vpc_Chained_Trl_Component::getChainedByMaster($masterPage, $l);
                } else if (is_instance_of($l->componentClass, 'Vpc_Root_TrlRoot_Master_Component')) {
                    $page = $masterPage;
                }
                $p = $page;
                while ($p && $page) {
                    //TODO dafür müsste es eine bessere methode geben
                    if (isset($p->row) && isset($p->row->visible) && !$p->row->visible) {
                        $page = null;
                    }
                    $p = $p->parent;
                }
            }
            $home = $l->getChildPage(array('home'=>true));
            if ($home) {
                $ret['languages'][] = array(
                    'language' => $l->id,
                    'home' => $home,
                    'page' => $page ? $page : $home,
                    'flag' => $l->getChildComponent('-flag'),
                    'name' => $l->name
                );
            }
        }
        if ($this->_getSetting('showCurrent') && count($ret['languages']) == 1) {
            $ret['languages'] = array();
        }
        return $ret;
    }

    public static function getStaticCacheVars()
    {
        $ret = Vpc_Menu_Abstract_Component::getStaticCacheVars();
        foreach (Vpc_Abstract::getComponentClasses() as $componentClass) {
            foreach (Vpc_Abstract::getSetting($componentClass, 'generators') as $key => $generator) {
                if (is_instance_of($generator['class'], 'Vpc_Chained_Abstract_ChainedGenerator')) {
                    $generator = current(Vps_Component_Generator_Abstract::getInstances(
                        $componentClass, array('generator' => $key))
                    );
                    $ret[] = array(
                        'model' => $generator->getModel()
                    );
                }
            }
        }
        return $ret;
    }
}
