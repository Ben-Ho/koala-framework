<?php
class Vpc_Root_Category_Generator extends Vps_Component_Generator_Abstract
{
    protected $_componentClass = 'row';
    protected $_idSeparator = false;
    protected $_loadTableFromComponent = false;
    protected $_inherits = true;

    protected $_pageDataLoaded = false;
    protected $_pageData = array();
    protected $_pageParent = array();
    protected $_pageFilename = array();
    protected $_pageComponentParent = array();
    protected $_pageComponent = array();
    private $_pageHome = null;

    protected function _loadPageData($parentData, $select)
    {
        if ($this->_pageDataLoaded) return;
        $this->_pageDataLoaded = true;


        if ($select->hasPart(Vps_Component_Select::WHERE_ID) &&
            isset($this->_pageData[$select->getPart(Vps_Component_Select::WHERE_ID)])
        ) {
             return;
        }

        $select = $this->_getModel()->select()->order('pos');
        $rows = $this->_getModel()->fetchAll($select)->toArray();
        foreach ($rows as $row) {
            $this->_pageData[$row['id']] = $row;
            $parentId = $row['parent_id'];
            $id = $row['id'];
            $this->_pageChilds[$parentId][] = $id;
            $this->_pageFilename[$row['filename']][$parentId] = $id;
            $this->_pageComponentParent[$row['component']][$parentId][] = $id;
            $this->_pageComponent[$row['component']][] = $id;
            if ($row['is_home']) $this->_pageHome[] = $id;
        }
    }

    protected function _formatSelectFilename(Vps_Component_Select $select)
    {
        return $select;
    }

    protected function _formatSelectHome(Vps_Component_Select $select)
    {
        return $select;
    }

    public function getChildIds($parentData, $select = array())
    {
        throw new Vps_Exception('Not supported yet');
    }

    public function getChildData($parentData, $select = array())
    {
        Vps_Benchmark::count('GenPage::getChildData');

        $this->_loadPageData($parentData, $select);

        $select = $this->_formatSelect($parentData, $select);
        if (is_null($select)) return array();
        $pageIds = $this->_getPageIds($parentData, $select);

        $ret = array();
        foreach ($pageIds as $pageId) {
            $page = $this->_pageData[$pageId];
            if ($select->hasPart(Vps_Component_Select::WHERE_SHOW_IN_MENU)) {
                $menu = $select->getPart(Vps_Component_Select::WHERE_SHOW_IN_MENU);
                if ($menu == $page['hide']) continue;
            }
            static $showInvisible;
            if (is_null($showInvisible)) {
                $showInvisible = Vps_Registry::get('config')->showInvisible;
            }
            if ($select->getPart(Vps_Component_Select::IGNORE_VISIBLE)) {
            } else if (!$showInvisible) {
                if (!$this->_pageData[$pageId]['visible']) continue;
            }
            $d = $this->_createData($parentData, $pageId, $select);
            if ($d) $ret[] = $d;

            if ($select->hasPart(Vps_Model_Select::LIMIT_COUNT)) {
                if (count($ret) >= $select->getPart(Vps_Model_Select::LIMIT_COUNT)) break;
            }
        }
        return $ret;
    }

    protected function _getPageIds($parentData, $select)
    {
        if (!$parentData && ($p = $select->getPart(Vps_Component_Select::WHERE_ON_SAME_PAGE))) {
            if ($p->getPage()) $p = $p->getPage();
            $parentData = $p;
        }
        $pageIds = array();
        if ($id = $select->getPart(Vps_Component_Select::WHERE_ID)) {
            if (isset($this->_pageData[$id])) {
                if ($select->hasPart(Vps_Component_Select::WHERE_COMPONENT_CLASSES)) {
                    $selectClasses = $select->getPart(Vps_Component_Select::WHERE_COMPONENT_CLASSES);
                    $class = $this->_settings['component'][$this->_pageData[$id]['component']];
                    if (in_array($class, $selectClasses)) {
                        $pageIds[] = $id;
                    }
                } else {
                    $pageIds[] = $id;
                }
            }
        } else if ($parentData) {
            // diese Abfragen sind implizit recursive=true
            $parentId = $parentData->dbId;
            if ($select->getPart(Vps_Component_Select::WHERE_HOME)) {
                foreach ($this->_pageHome as $pageId) {
                    if (substr($this->_pageData[$pageId]['parent_id'], 0, strlen($parentId)) == $parentId) {
                        $pageIds[] = $pageId;
                    } else {
                        $id = $pageId;
                        while (true) {
                            if ($this->_pageData[$id]['parent_id'] == $parentId) {
                                $pageIds[] = $pageId;
                                break;
                            }
                            $id = $this->_pageData[$id]['parent_id'];
                            if (!isset($this->_pageData[$id])) break;
                        }
                    }
                }
            } else if ($select->hasPart(Vps_Component_Select::WHERE_FILENAME)) {
                $filename = $select->getPart(Vps_Component_Select::WHERE_FILENAME);
                if (isset($this->_pageFilename[$filename])) {
                    foreach ($this->_pageFilename[$filename] as $pId => $pageId) {
                        if (substr($pId, 0, strlen($parentId)) == $parentId)
                            $pageIds[] = $pageId;
                    }
                }
            } else if ($select->hasPart(Vps_Component_Select::WHERE_COMPONENT_CLASSES)) {
                $selectClasses = $select->getPart(Vps_Component_Select::WHERE_COMPONENT_CLASSES);
                $keys = array();
                foreach ($selectClasses as $selectClass) {
                    $key = array_search($selectClass, $this->_settings['component']);
                    if ($key) $keys[] = $key;
                }
                foreach (array_unique($keys) as $key) {
                    if (isset($this->_pageComponentParent[$key])) {
                        foreach ($this->_pageComponentParent[$key] as $pId => $ids) {
                            if (substr($pId, 0, strlen($parentId)) == $parentId) {
                                $pageIds = array_merge($pageIds, $ids);
                            }
                        }
                    }
                }
            } else {
                if (isset($this->_pageChilds[$parentData->componentId])) {
                    $pageIds = $this->_pageChilds[$parentData->componentId];
                }
            }
        } else if ($select->hasPart(Vps_Component_Select::WHERE_COMPONENT_CLASSES)) {
            $selectClasses = $select->getPart(Vps_Component_Select::WHERE_COMPONENT_CLASSES);
            $keys = array();
            foreach ($selectClasses as $selectClass) {
                $key = array_search($selectClass, $this->_settings['component']);
                if ($key) $keys[] = $key;
            }
            foreach ($keys as $key) {
                if (isset($this->_pageComponent[$key])) {
                    $pageIds = array_merge($pageIds, $this->_pageComponent[$key]);
                }
            }
        } else {
            throw new Vps_Exception("This would return all pages. You don't want this.");
        }
        return $pageIds;
    }

    protected function _createData($parentData, $id, $select)
    {
        $page = $this->_pageData[$id];

        if (!$parentData || ($parentData->componentClass == $this->_class && $page['parent_id'])) {
            $c = array();
            if ($select->hasPart(Vps_Component_Select::IGNORE_VISIBLE)) {
                $c['ignoreVisible'] = $select->getPart(Vps_Component_Select::IGNORE_VISIBLE);
            }
            $parentData = Vps_Component_Data_Root::getInstance()
                                ->getComponentById($page['parent_id'], $c);
        }
        if (!$parentData) return null;
        if ((int)$parentData->componentId == 0 && $parentData->componentClass != $this->_class) return null;
        return parent::_createData($parentData, $id, $select);
    }

    protected function _formatConfig($parentData, $id)
    {
        $data = array();
        $page = $this->_pageData[$id];
        $data['filename'] = $page['filename'];
        $data['rel'] = '';
        $data['name'] = $page['name'];
        $data['isPage'] = true;
        $data['isPseudoPage'] = true;
        $data['componentId'] = $page['id'];
        $data['componentClass'] = $this->_getChildComponentClass($page['component']);
        $data['row'] = (object)$page;
        $data['parent'] = $parentData;
        $data['isHome'] = $page['is_home'];
        $data['visible'] = $page['visible'];
        if (isset($page['tags']) && $page['tags']) {
            $data['tags'] = explode(',', $page['tags']);
        } else {
            $data['tags'] = array();
        }
        return $data;
    }
    protected function _getIdFromRow($id)
    {
        return $id;
    }

    protected function _getDataClass($config, $id)
    {
        if ($this->_pageData[$id]['is_home']) {
            return 'Vps_Component_Data_Home';
        } else {
            return parent::_getDataClass($config, $id);
        }
    }

    public function getGeneratorFlags()
    {
        $ret = parent::getGeneratorFlags();
        $ret['showInPageTreeAdmin'] = true;
        $ret['pseudoPage'] = true;
        $ret['page'] = true;
        $ret['pageGenerator'] = true;
        return $ret;
    }
}
