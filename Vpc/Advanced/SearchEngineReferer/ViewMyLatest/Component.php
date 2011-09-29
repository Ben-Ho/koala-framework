<?php
class Vpc_Advanced_SearchEngineReferer_ViewMyLatest_Component
    extends Vpc_Advanced_SearchEngineReferer_ViewLatest_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['placeholder']['header'] = trlVps('This site was latest found by').':';
        return $ret;
    }

    protected function _getSelect()
    {
        $select = parent::_getSelect();
        return $select->whereEquals('component_id', $this->getData()->parent->componentId);
    }
}
