<?php
class Vpc_Chained_Cc_Component extends Vpc_Abstract
{
    public static function getSettings($masterComponentClass)
    {
        $ret = parent::getSettings();
        if (!$masterComponentClass) {
            throw new Vps_Exception("This component requires a parameter");
        }
        $ret['masterComponentClass'] = $masterComponentClass;
        $ret['generators'] = Vpc_Abstract::getSetting($masterComponentClass, 'generators');
        foreach ($ret['generators'] as $k=>&$g) {
            if (!is_array($g['component'])) $g['component'] = array($k=>$g['component']);
            foreach ($g['component'] as $key=>&$c) {
                if (!$c) continue;
                $masterC = $c;
                $c = Vpc_Admin::getComponentClass($c, 'Cc_Component');
                if (!$c) $c = 'Vpc_Chained_Cc_Component';
                $c .= '.'.$masterC;
                $g['masterComponentsMap'][$masterC] = $key;
            }
            $g['chainedGenerator'] = $g['class'];
            $g['class'] = 'Vpc_Chained_Cc_Generator';
            if (isset($g['dbIdShortcut'])) unset($g['dbIdShortcut']);
        }
        foreach (array('componentName', 'componentIcon', 'editComponents') as $i) {
            if (Vpc_Abstract::hasSetting($masterComponentClass, $i)) {
                $ret[$i] = Vpc_Abstract::getSetting($masterComponentClass, $i);
            }
        }

        foreach (array('showInPageTreeAdmin', 'processInput', 'menuCategory') as $f) {
            $flags = Vpc_Abstract::getSetting($masterComponentClass, 'flags', false);
            if (isset($flags[$f])) {
                $ret['flags'][$f] = $flags[$f];
            }
        }
        return $ret;
    }

    public function preProcessInput($postData)
    {
        $c = $this->getData()->chained->getComponent();
        if (method_exists($c, 'preProcessInput')) {
            $c->preProcessInput($postData);
        }
    }

    public function processInput($postData)
    {
        $c = $this->getData()->chained->getComponent();
        if (method_exists($c, 'processInput')) {
            $c->processInput($postData);
        }
    }

    public function getTemplateVars()
    {
        $data = $this->getData();
        $ret = $data->chained->getComponent()->getTemplateVars();
        $ret['data'] = $data;
        $ret['chained'] = $data->chained;
        $ret['linkTemplate'] = self::getTemplateFile($data->chained->componentClass);

        $ret['componentClass'] = get_class($this);

        $ret['placeholder'] = Vpc_Abstract::getSetting($data->chained->componentClass, 'placeholder');
        foreach ($ret['placeholder'] as $k => $v) {
            $ret['placeholder'][$k] = $this->getData()->trlStaticExecute($v);
        }
        return $ret;
    }

    public function getCacheVars()
    {
        $ret = parent::getCacheVars();
        $ret = array_merge($ret, $this->getData()->chained->getComponent()->getCacheVars());
        return $ret;
    }

    public function getPartialClass()
    {
        return $this->getData()->chained->getComponent()->getPartialClass();
    }

    public function getPartialParams()
    {
        return $this->getData()->chained->getComponent()->getPartialParams();
    }

    public function getPartialVars($partial, $nr, $info)
    {
        $ret = $this->getData()->chained->getComponent()->getPartialVars($partial, $nr, $info);
        $ret['linkTemplate'] = self::getTemplateFile($this->getData()->chained->componentClass, 'Partial');
        return $ret;
    }

    public function getPartialCacheVars($nr)
    {
        return $this->getData()->chained->getComponent()->getPartialCacheVars($nr);
    }

    public static function getStaticCacheVars($componentClass)
    {
        $cls = substr($componentClass, strpos($componentClass, '.')+1);
        $cls = strpos($cls, '.') ? substr($cls, 0, strpos($cls, '.')) : $cls;
        return call_user_func(array($cls, 'getStaticCacheVars'), $cls);
    }


    public static function getChainedByMaster($masterData, $chainedData, $select = array())
    {
        if (!$masterData) return null;

        while ($chainedData) {
            if (Vpc_Abstract::getFlag($chainedData->componentClass, 'subroot') == 'cc') {
                break;
            }
            $chainedData = $chainedData->parent;
        }

        $c = $masterData;
        $ids = array();
        $hasLanguageReached = false;
        while ($c) {
            $pos = max(
                strrpos($c->componentId, '-'),
                strrpos($c->componentId, '_')
            );
            $id = substr($c->componentId, $pos);
            if (Vpc_Abstract::getFlag($chainedData->componentClass, 'subroot') == 'cc') {
                $hasLanguageReached = true;
                break;
            }
            $skipParents = false;
            if ((int)$id > 0) { // nicht mit is_numeric wegen Bindestrich, das als minus interpretiert wird
                $id = '_' . $id;
                $skipParents = true;
            }
            $c = $c->parent;
            if ($c) {
                $ids[] = $id;
                //bei pages die parent ids auslassen
                if ($skipParents) {
                    while (is_numeric($c->componentId)) {
                        $c = $c->parent;
                    }
                }
            }
        }
        if (!$hasLanguageReached) return $masterData;
        $ret = $chainedData;
        if (is_array($select)) {
            $select = new Vps_Component_Select($select);
        }
        foreach (array_reverse($ids) as $id) {
            $select->whereId($id);
            $ret = $ret->getChildComponent($select);
            if (!$ret) return null;
        }
        return $ret;
    }
}
