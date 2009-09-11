<?php
class Vpc_Posts_Latest_Component extends Vpc_Abstract
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['componentName'] = trlVps('Posts.Last Posts');
        $ret['modelname'] = 'Vpc_Posts_Directory_Model';
        $ret['numberOfPosts'] = 9;
        return $ret;
    }

    protected function _getSelect()
    {
        $select = new Vps_Model_Select();
        $select
            ->whereEquals('visible', 1)
            ->order('create_time', 'DESC')
            ->limit($this->_getSetting('numberOfPosts'));
        return $select;
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        $ret['posts'] = array();
        $rows = $this->getModel()->fetchAll($this->_getSelect());
        foreach ($rows as $row) {
            $id = $row->component_id . '-' . $row->id;
            $post = Vps_Component_Data_Root::getInstance()->getComponentByDbId($id);
            if ($post) {
                $dateHelper = new Vps_View_Helper_Date();
                $linktexts = array();
                $page = $post->getPage();
                while ($page) {
                    $linktexts[] = $page->name;
                    $page = $page->getParentPage();
                }
                $post->linktext =
                    $dateHelper->date($post->row->create_time) .
                    ': ' .
                    implode(' &raquo; ', array_reverse($linktexts));
                $ret['posts'][] = $post;
            }
        }
        return $ret;
    }

    public function getStaticCacheVars()
    {
        $ret = array();
        $ret[] = array(
            'model' => 'Vpc_Posts_Directory_Model'
        );
        $ret[] = array(
            'model' => Vps_Registry::get('config')->user->model
        );
        return $ret;
    }
}
