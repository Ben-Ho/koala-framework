<?php
class Vps_Component_Data_Root extends Vps_Component_Data
{
    private static $_instance;
    private static $_rootComponentClass;
    private $_hasChildComponentCache;
    private $_componentsByClassCache;
    private $_componentsByDbIdCache;
    private $_generatorsForClassesCache = array();
    private $_currentPage;
    private $_pageGenerators;

    public function __construct($config = array())
    {
        $config = array_merge(array(
                'name' => 'Root',
                'parent' => null,
                'isPage' => false,
                'isPseudoPage' => false,
                'inherits' => true,
                'componentId' => 'root',
                'filename' => false
            ), $config
        );
        parent::__construct($config);
        $this->_inheritClasses = array();
        $this->_uniqueParentDatas = array();
    }

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            $c = self::getComponentClass();
            if ($c) {
                self::$_instance = new self(array('componentClass' => $c));
            } else {
                self::$_instance = false;
            }
        }
        return self::$_instance;
    }

    public static function getComponentClass()
    {
        if (is_null(self::$_rootComponentClass)) {
            if (Vps_Registry::get('config')->vpc->rootComponent) {
                self::$_rootComponentClass = Vps_Registry::get('config')->vpc->rootComponent;
            } else {
                self::$_rootComponentClass = false;
            }
        }
        return self::$_rootComponentClass;
    }

    public static function setComponentClass($componentClass)
    {
        self::$_rootComponentClass = $componentClass;
        self::$_instance = null;
        Vps_Component_Abstract::resetSettingsCache();
    }

    public function getPageByUrl($url)
    {
        $parsedUrl = parse_url($url);
        $path = $this->getComponent()->formatPath($parsedUrl);
        $path = trim($path, '/');
        if ($path == '') {
            $ret = $this->getChildPage(array('home' => true));
        } else {
            foreach (Vpc_Abstract::getComponentClasses() as $c) {
                if (Vpc_Abstract::getFlag($c, 'shortcutUrl')) {
                    $ret = call_user_func(array($c, 'getDataByShortcutUrl'), $c, $path);
                    if ($ret) return $ret;
                }
            }
            $ret = $this->getChildPageByPath($path);
            if ($parsedUrl['path'] == '' || $parsedUrl['path'] == '/') {
                $ret = $ret->getChildPage(array('home' => true));
            }
        }
        return $ret;
    }

    public function getComponentById($componentId, $select = array())
    {
        if (is_array($select)) {
            $select = new Vps_Component_Select($select);
        } else {
            $select = clone $select;
        }
        $ret = $this;
        $idParts = $this->_getIdParts($componentId);
        foreach ($idParts as $i=>$idPart) {
            if ($idPart == 'root') {
                $ret = $this;
            } else {
                if ($i+1 == count($idParts)) {
                    //nur bei letzem part select berücksichtigen
                    $select->whereId($idPart);
                    $s = $select;
                } else {
                    $s = array('id'=>$idPart);
                    if ($select->hasPart(Vps_Component_Select::IGNORE_VISIBLE)) {
                        //ignoreVisible doch mitnehmen damit wir unterkomponeten von unsichtbaren
                        //komponenten finden
                        $s['ignoreVisible'] = $select->getPart(Vps_Component_Select::IGNORE_VISIBLE);
                    }
                    if ($select->hasPart(Vps_Component_Select::WHERE_SUBROOT)) {
                        //ignoreVisible doch mitnehmen damit wir unterkomponeten von unsichtbaren
                        //komponenten finden
                        $s['subroot'] = $select->getPart(Vps_Component_Select::WHERE_SUBROOT);
                    }
                    $s = new Vps_Component_Select($s);
                }

                if ($i == 0) { // Muss eine Page sein
                    $generators = $this->getPageGenerators();
                    foreach ($generators as $generator) {
                        $ret = array_pop($generator->getChildData(null, $s));
                        if ($ret) break;
                    }
                } else {
                    $ret = $ret->getChildComponent($s);
                }
                if (!$ret) break;
            }
        }
        return $ret;
    }

    public function getPageGenerators()
    {
        if (!is_null($this->_pageGenerators)) return $this->_pageGenerators;

        static $cache = null;
        if (!$cache) {
            $cache = Vps_Cache::factory('Core', 'Memcached', array(
                'lifetime'=>null,
                'automatic_cleaning_factor' => false,
                'automatic_serialization'=>true));
        }
        $cacheId = $this->componentClass . '_pageGenerators';

        $generators = $cache->load($cacheId);
        if (!$generators) {
            $generators = array();
            foreach (Vpc_Abstract::getComponentClasses() as $class) {
                foreach (Vpc_Abstract::getSetting($class, 'generators') as $key => $generator) {
                    if (is_instance_of($generator['class'], 'Vps_Component_Generator_Page')) {
                        $generators[] = array('class' => $class, 'key' => $key, 'generator' => $generator);
                    }
                }
            }
            $cache->save($generators, $cacheId);
        }

        $this->_pageGenerators = array();
        foreach ($generators as $g) {
            $this->_pageGenerators[] = Vps_Component_Generator_Abstract::getInstance(
                $g['class'], $g['key'], $g['generator']
            );
        }
        return $this->_pageGenerators;
    }

    private function _getIdParts($componentId)
    {
        $ret = array();
        $ids = preg_split('/([_\-])/', $componentId, -1, PREG_SPLIT_DELIM_CAPTURE);
        for ($i = 0; $i < count($ids); $i++) {
            if ($ids[$i] == '') {
                $i++;
            }
            $idPart = $ids[$i];
            if ($i > 0) {
                $i++;
                $idPart .= $ids[$i];
            }
            $ret[] = $idPart;
        }
        return $ret;
    }

    public function getComponentByDbId($dbId, $select = array())
    {
        $components = $this->getComponentsByDbId($dbId, $select);
        $this->_checkSingleComponent($components);
        if (isset($components[0])) {
            return $components[0];
        }
        return null;
    }

    public function getComponentsByDbId($dbId, $select = array())
    {
        if (is_array($select)) {
            $select = new Vps_Component_Select($select);
        }

        $cacheId = $dbId.$select->getHash();
        if (!isset($this->_componentsByDbIdCache[$cacheId])) {
            $benchmark = Vps_Benchmark::start();

            if (is_numeric(substr($dbId, 0, 1)) || substr($dbId, 0, 4)=='root') {
                $idSelect = clone $select;
                if ($idSelect->hasPart('whereSubroot')) {
                    $subroot = $idSelect->getPart('whereSubroot');
                    $idSelect->unsetPart('whereSubroot');
                    $idComponent = $this->getComponentById($dbId, $select);
                }
                $data = $this->getComponentById($dbId, $select);
                if ($data) {
                    return array($data);
                } else {
                    return array();
                }
            }

            if ($select->hasPart(Vps_Component_Select::LIMIT_COUNT)) {
                $limitCount = $select->getPart(Vps_Component_Select::LIMIT_COUNT);
            }
            $ret = array();
            foreach (Vpc_Abstract::getComponentClasses() as $class) {
                foreach (Vpc_Abstract::getSetting($class, 'generators') as $key => $generator) {
                    if (isset($generator['dbIdShortcut'])
                            && substr($dbId, 0, strlen($generator['dbIdShortcut'])) == $generator['dbIdShortcut']) {
                        $idParts = $this->_getIdParts(substr($dbId, strlen($generator['dbIdShortcut']) - 1));
                        $generator = Vps_Component_Generator_Abstract::getInstance($class, $key);
                        if (count($idParts) <= 1) {
                            $generatorSelect = clone $select;
                        } else {
                            $generatorSelect = new Vps_Component_Select(); // Select erst bei letzten Part
                            if ($select->hasPart(Vps_Component_Select::IGNORE_VISIBLE)) {
                                $generatorSelect->ignoreVisible($select->getPart(Vps_Component_Select::IGNORE_VISIBLE));
                            }
                            if ($select->hasPart(Vps_Component_Select::WHERE_SUBROOT)) {
                                $generatorSelect->whereSubroot($select->getPart(Vps_Component_Select::WHERE_SUBROOT));
                            }
                        }
                        if (isset($limitCount)) {
                            $generatorSelect->limit($limitCount - count($ret));
                        }
                        $generatorSelect->whereId($idParts[0]);
                        $data = $generator->getChildData(null, $generatorSelect);
                        unset($idParts[0]);
                        foreach ($data as $d) {
                            $componentId = $d->componentId . implode('', $idParts);
                            $data = $this->getComponentById($componentId, $select);
                            if ($data) {
                                $ret[] = $data;
                            }
                            if (isset($limitCount) && $limitCount - count($ret) <= 0) {
                                break 3;
                            }
                        }
                    }
                }
            }
            $this->_componentsByDbIdCache[$cacheId] = $ret;
        }
        return $this->_componentsByDbIdCache[$cacheId];
    }
    public function getComponentsByClass($class, $select = array())
    {
        if (is_array($select)) {
            $select = new Vps_Component_Select($select);
        }
        $cacheId = $class.$select->getHash();
        if (!isset($this->_componentsByClassCache[$cacheId])) {
            $benchmark = Vps_Benchmark::start();

            $lookingForChildClasses = Vpc_Abstract::getComponentClassesByParentClass($class);
            foreach ($lookingForChildClasses as $c) {
                if (is_instance_of($c, 'Vpc_Root_Component')) {
                    return array($this);
                }
            }
            $ret = $this->getComponentsBySameClass($lookingForChildClasses, $select);
            $this->_componentsByClassCache[$cacheId] = $ret;

            if ($benchmark) $benchmark->stop();
        }
        return $this->_componentsByClassCache[$cacheId];
    }

    public function getComponentsBySameClass($lookingForChildClasses, $select = array())
    {
        if (!is_array($lookingForChildClasses) &&
            is_instance_of($lookingForChildClasses, 'Vpc_Root_Abstract')
        ) {
            return array($this);
        }

        if (!is_array($lookingForChildClasses)) {
            $lookingForChildClasses = array($lookingForChildClasses);
        }

        if (is_array($select)) {
            $select = new Vps_Component_Select($select);
        }
        $select->whereComponentClasses($lookingForChildClasses);

        if ($select->hasPart(Vps_Component_Select::LIMIT_COUNT)) {
            $limitCount = $select->getPart(Vps_Component_Select::LIMIT_COUNT);
        }

        $ret = array();
        foreach ($this->_getGeneratorsForClasses($lookingForChildClasses) as $generator) {
            foreach ($generator->getChildData(null, $select) as $data) {
                $ret[] = $data;
                if (isset($limitCount) && $limitCount - count($ret) <= 0) {
                    return $ret;
                }
            }
        }
        return $ret;
    }

    private function _getGeneratorsForClasses($lookingForClasses)
    {

        static $cache = null;
        if (!$cache) {
            $cache = Vps_Cache::factory('Core', 'Memcached', array(
                'lifetime'=>null,
                'automatic_cleaning_factor' => false,
                'automatic_serialization'=>true));
        }

        $cacheId = 'genForCls'.$this->getComponentClass().implode('', $lookingForClasses);
        if (isset($this->_generatorsForClassesCache[$cacheId])) {
            Vps_Benchmark::count('_getGeneratorsForClasses hit', implode(', ', $lookingForClasses));
        } else if (($generators = $cache->load($cacheId)) !== false) {
            $ret = array();
            foreach ($generators as $g) {
                $ret[] = Vps_Component_Generator_Abstract::getInstance($g[0], $g[1]);
            }
            $this->_generatorsForClassesCache[$cacheId] = $ret;
            Vps_Benchmark::count('_getGeneratorsForClasses semi-hit', implode(', ', $lookingForClasses));
        } else {
            Vps_Benchmark::count('_getGeneratorsForClasses miss', implode(', ', $lookingForClasses));
            $generators = array();
            foreach (Vpc_Abstract::getComponentClasses() as $c) {
                foreach (Vpc_Abstract::getSetting($c, 'generators') as $key => $generator) {
                    if (is_array($generator['component'])) {
                        $childClasses = $generator['component'];
                    } else {
                        $childClasses = array($generator['component']);
                    }
                    foreach ($childClasses as $childClass) {
                        if (in_array($childClass, $lookingForClasses)) {
                            $generators[$c.$key] = array($c, $key);
                        }
                    }
                }
            }
            $generators = array_values($generators);
            $cache->save($generators, $cacheId);
            $ret = array();
            foreach ($generators as $g) {
                $ret[] = Vps_Component_Generator_Abstract::getInstance($g[0], $g[1]);
            }
            $this->_generatorsForClassesCache[$cacheId] = $ret;
        }
        return $this->_generatorsForClassesCache[$cacheId];
    }

    public function getComponentByClass($class, $select = array())
    {
        $components = $this->getComponentsByClass($class, $select);
        $this->_checkSingleComponent($components);
        if (isset($components[0])) {
            return $components[0];
        }
        return null;
    }

    public function getComponentBySameClass($class, $select = array())
    {
        $components = $this->getComponentsBySameClass($class, $select);
        $this->_checkSingleComponent($components);
        if (isset($components[0])) {
            return $components[0];
        }
        return null;
    }

    private function _checkSingleComponent($components)
    {
        if (count($components) > 1) {
            $ids = array();
            foreach ($components as $c) {
                $ids[] = $c->componentId;
            }
            throw new Vps_Exception('getComponentByXxx must not get more than one component but got these: ' . implode(', ', $ids));
        }
    }

    public function setCurrentPage(Vps_Component_Data $page)
    {
        $this->_currentPage = $page;
    }

    public function getCurrentPage()
    {
        return $this->_currentPage;
    }

    public function setFilename($f)
    {
        $this->_filename = $f;
    }
}
?>
