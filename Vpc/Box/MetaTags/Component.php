<?php
class Vpc_Box_MetaTags_Component extends Vpc_Abstract
{
    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        $components = array();
        /*
        $components = $this->getData()->getPage()->getRecursiveChildComponents(array(
            'page' => false,
            'flags' => array('metaTags' => true)
        ));*/
        if (Vpc_Abstract::getFlag($this->getData()->getPage()->componentClass, 'metaTags')) {
            $components[] = $this->getData()->getPage();
        }
        $ret['metaTags'] = array();
        foreach ($components as $component) {
            foreach ($component->getComponent()->getMetaTags() as $name=>$content) {
                if (!isset($ret['metaTags'][$name])) $ret['metaTags'][$name] = '';
                //TODO: bei zB noindex,nofollow anderes trennzeichen
                $ret['metaTags'][$name] .= ' '.$content;
            }
        }
        foreach ($ret['metaTags'] as &$i) $i = trim($i);
        /*
        $components = $this->getData()->getPage()->getRecursiveChildComponents(array(
            'page' => false,
            'limit' => 1,
            'flags' => array('noIndex' => true)
        ));*/
        if (/*$components || */Vpc_Abstract::getFlag($this->getData()->getPage()->componentClass, 'noIndex')) {
            if (isset($ret['metaTags']['robots'])) {
                $ret['metaTags']['robots'] .= ',';
            } else {
                $ret['metaTags']['robots'] = '';
            }
            $ret['metaTags']['robots'] .= 'noindex';
        }
        return $ret;
    }
}
