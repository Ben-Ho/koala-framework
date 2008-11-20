<?php
class Vpc_Advanced_SearchEngineReferer_ViewLatest_Component
    extends Vpc_Abstract
{
    private $_referersCache = null;
    private $_parentModel;

    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['limit'] = 5;
        $ret['viewCache'] = false;
        return $ret;
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        $ret['referers'] = $this->_getReferers();
        return $ret;
    }

    protected function _getParentModel()
    {
        if (!isset($this->_parentModel)) {
            $this->_parentModel = $this->getData()->parent->getComponent()->getModel();
        }
        return $this->_parentModel;
    }

    private function _getReferers()
    {
        if (is_null($this->_referersCache)) {
            $model = $this->_getParentModel();
            $rowset = $model->getRows($this->_getSelect());

            $this->_referersCache = array();
            foreach ($rowset as $row) {
                $host = parse_url($row->referer_url, PHP_URL_HOST);
                $this->_referersCache[] = array(
                    'component' => Vps_Component_Data_Root::getInstance()->getComponentByDbId($row->component_id),
                    'row'       => $row,
                    'host'      => $host,
                    'query'     => Vpc_Advanced_SearchEngineReferer_Component::getQueryVar($row->referer_url)
                );
            }
        }
        return $this->_referersCache;
    }

    public function hasContent()
    {
        $refs = $this->_getReferers();
        return count($refs) ? true : false;
    }

    protected function _getSelect()
    {
        $model = $this->_getParentModel();
        return $model->select()
            ->order('id', 'DESC')
            ->limit($this->_getSetting('limit'));
    }
}
