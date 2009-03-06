<?php
class Vps_Assets_Dependencies
{
    private $_files = array();
    private $_config;
    private $_dependenciesConfig;
    private $_processedDependencies = array();
    private $_processedComponents = array();
    /**
     * @param Zend_Config für tests
     **/
    public function __construct($config = null)
    {
        if (!$config) {
            $config = Vps_Registry::get('config');
        }
        $this->_config = $config;
    }

    public function getAssetUrls($assetsType, $fileType, $section, $rootComponent)
    {
        $b = Vps_Benchmark::start();
        if ($this->_config->debug->menu) {
            $session = new Zend_Session_Namespace('debug');
            if (isset($session->enable) && $session->enable) {
                $assetsType .= 'Debug';
            }
        }
        $ret = array();
        if (!$this->_config->debug->assets->$fileType || (isset($session->$fileType) && !$session->$fileType)) {
            $v = $this->_config->application->version;
            $language = Zend_Registry::get('trl')->getTargetLanguage();
            $ret[] = "/assets/all/$section/"
                            .($rootComponent?$rootComponent.'/':'')
                            ."$language/$assetsType.$fileType?v=$v";
            $allUsed = true;
        }

        foreach ($this->getAssetFiles($assetsType, $fileType, $section, $rootComponent) as $file) {
            if ($file instanceof Vps_Assets_Dynamic) {
                $file = $file->getFile();
            }
            if (substr($file, 0, 7) == 'http://' || substr($file, 0, 8) == 'https://' || substr($file, 0, 1) == '/') {
                $ret[] = $file;
            } else if (empty($allUsed)) {
                $ret[] = "/assets/$file";
            }
        }
        return $ret;
    }

    public function getAssetFiles($assetsType, $fileType, $section, $rootComponent)
    {
        if (!isset($this->_files[$assetsType])) {
            $cacheId = 'dependencies'.str_replace(':', '_', $assetsType).$rootComponent;
            $cache = Vps_Assets_Cache::getInstance();
            $this->_files[$assetsType] = $cache->load($cacheId);
            if ($this->_files[$assetsType]===false) {
                Vps_Benchmark::count('processing dependencies miss', $assetsType);
                $this->_files[$assetsType] = array();
                if (!isset($this->_config->assets->$assetsType)) {
                    if (strpos($assetsType, ':')) {
                        $configPath = str_replace('_', '/', substr($assetsType, 0, strpos($assetsType, ':')));
                        foreach(explode(PATH_SEPARATOR, get_include_path()) as $dir) {
                            if (file_exists($dir.'/'.$configPath.'/config.ini')) {
                                $sect = 'vivid';
                                $configFull = new Zend_Config_Ini($dir.'/'.$configPath.'/config.ini', null);
                                if (isset($configFull->{Vps_Setup::getConfigSection()})) {
                                    $sect = Vps_Setup::getConfigSection();
                                }
                                $config = clone Vps_Registry::get('config');
                                $config->merge(new Zend_Config_Ini($dir.'/'.$configPath.'/config.ini', $sect));
                                break;
                            }
                        }
                        if (!isset($config)) {
                            throw new Vps_Assets_NotFoundException("Unknown AssetsType '$assetsType'");
                        }
                        $assets = $config->assets->{substr($assetsType, strpos($assetsType, ':')+1)};
                    }
                } else {
                    $assets = $this->_config->assets->$assetsType;
                }
                foreach ($assets as $d=>$v) {
                    if ($v) {
                        $this->_processDependency($assetsType, $d, $rootComponent);
                    }
                }
                foreach ($this->_files[$assetsType] as $f) {
                    if (is_string($f)) {
                        $this->getAssetPath($f); //wirft exception wenn datei nicht gefunden
                    }
                }
                $cache->save($this->_files[$assetsType], $cacheId);
            }
        }

        if (is_null($fileType)) {
            $files = $this->_files[$assetsType];
        } else {
            $files = array();
            foreach ($this->_files[$assetsType] as $file) {
                if ((is_string($file) && substr($file, -strlen($fileType)-1) == '.'.$fileType)
                    || ($file instanceof Vps_Assets_Dynamic && $file->getType() == $fileType)) {
                    if (is_string($file) && substr($file, -strlen($fileType)-1) == " $fileType") {
                        //wenn asset hinten mit " js" aufhört das js abschneiden
                        //wird benötigt für googlemaps wo die js-dateien kein js am ende haben
                        $file = substr($file, 0, -strlen($fileType)-1);
                    }
                    $files[] = $file;
                }
            }
        }
        //hack: übersetzung immer zuletzt anhängen
        if ($fileType == 'js') {
            $files[] = 'vps/Ext/ext-lang-en.js';
        }

        foreach ($files as &$f) {
            if (is_string($f)) $f = $section . '-' . $f;
        }
        return $files;
    }

    private function _getDependenciesConfig($assetsType)
    {
        if (!isset($this->_dependenciesConfig[$assetsType])) {
            $ret = new Zend_Config_Ini(VPS_PATH.'/config.ini', 'dependencies',
                                                array('allowModifications'=>true));
            $ret->merge(new Zend_Config_Ini('application/config.ini', 'dependencies'));
            if (strpos($assetsType, ':')) {
                $configPath = str_replace('_', '/', substr($assetsType, 0, strpos($assetsType, ':')));
                foreach(explode(PATH_SEPARATOR, get_include_path()) as $dir) {
                    if (file_exists($dir.'/'.$configPath.'/config.ini')) {
                        $ret->merge(new Zend_Config_Ini($dir.'/'.$configPath.'/config.ini',  'dependencies'));
                        break;
                    }
                }
            }
            $this->_dependenciesConfig[$assetsType] = $ret;
        }
        return $this->_dependenciesConfig[$assetsType];
    }

    private function _processDependency($assetsType, $dependency, $rootComponent)
    {
        if (in_array($assetsType.$dependency, $this->_processedDependencies)) return;
        $this->_processedDependencies[] = $assetsType.$dependency;
        if ($dependency == 'Components' || $dependency == 'ComponentsAdmin') {
            $this->_processComponentDependency($assetsType, $rootComponent, $rootComponent, $dependency == 'ComponentsAdmin');
            return;
        }
        if (!isset($this->_getDependenciesConfig($assetsType)->$dependency)) {
            throw new Vps_Exception("Can't resolve dependency '$dependency'");
        }
        $deps = $this->_getDependenciesConfig($assetsType)->$dependency;

        if (isset($deps->dep)) {
            foreach ($deps->dep as $d) {
                $this->_processDependency($assetsType, $d, $rootComponent);
            }
        }

        if (isset($deps->files)) {
            foreach ($deps->files as $file) {
                $this->_processDependencyFile($assetsType, $file, $rootComponent);
            }
        }
        return;
    }

    private function _hasFile($assetsType, $file)
    {
        //in_array scheint mit php 5.1 mit objekten nicht zu funktionieren
        foreach ($this->_files[$assetsType] as $f) {
            if (gettype($f) == gettype($file) && $f == $file) {
                return true;
            }
        }
        return false;
    }

    private function _processComponentDependency($assetsType, $class, $rootComponent, $includeAdminAssets)
    {
        if (in_array($assetsType.$class.$includeAdminAssets, $this->_processedComponents)) return;

        $assets = Vpc_Abstract::getSetting($class, 'assets');
        $assetsAdmin = array();
        if ($includeAdminAssets) {
            $assetsAdmin = Vpc_Abstract::getSetting($class, 'assetsAdmin');
        }
        $this->_processedComponents[] = $assetsType.$class.$includeAdminAssets;
        if (isset($assets['dep'])) {
            foreach ($assets['dep'] as $dep) {
                $this->_processDependency($assetsType, $dep, $rootComponent);
            }
        }
        if (isset($assetsAdmin['dep'])) {
            foreach ($assetsAdmin['dep'] as $dep) {
                $this->_processDependency($assetsType, $dep, $rootComponent);
            }
        }
        if (isset($assets['files'])) {
            foreach ($assets['files'] as $file) {
                $this->_processDependencyFile($assetsType, $file, $rootComponent);
            }
        }
        if (isset($assetsAdmin['files'])) {
            foreach ($assetsAdmin['files'] as $file) {
                $this->_processDependencyFile($assetsType, $file, $rootComponent);
            }
        }

        //alle css-dateien der vererbungshierache includieren
        $componentCssFiles = array();

        foreach (Vpc_Abstract::getParentClasses($class) as $c) {
            $curClass = $c;
            if (substr($curClass, -10) == '_Component') {
                $curClass = substr($curClass, 0, -10);
            }
            $curClass =  $curClass . '_Component';
            $file = str_replace('_', DIRECTORY_SEPARATOR, $curClass);
            foreach ($this->_config->path as $type=>$dir) {
                if ($dir == '.') $dir = getcwd();
                if (is_file($dir . '/' . $file.'.css')) {
                    $f = $type . '/' . $file.'.css';
                    if (!$this->_hasFile($assetsType, $f)) {
                        $componentCssFiles[] = $f;
                    }
                }
                if (is_file($dir . '/' . $file.'.printcss')) {
                    $f = $type . '/' . $file.'.printcss';
                    if (!$this->_hasFile($assetsType, $f)) {
                        $componentCssFiles[] = $f;
                    }
                }
            }
        }
        //reverse damit css von weiter unten in der vererbungshierachie überschreibt
        $this->_files[$assetsType] = array_merge($this->_files[$assetsType], array_reverse($componentCssFiles));

        $classes = Vpc_Abstract::getChildComponentClasses($class);
        $classes = array_merge($classes, Vpc_Abstract::getSetting($class, 'plugins'));
        foreach ($classes as $class) {
            if ($class) {
                $this->_processComponentDependency($assetsType, $class, $rootComponent, $includeAdminAssets);
            }
        }
    }

    private function _processDependencyFile($assetsType, $file)
    {
        if (is_string($file) && substr($file, -2)=="/*") {
            $pathType = substr($file, 0, strpos($file, '/'));
            if (!isset($this->_config->path->$pathType)) {
                throw new Vps_Exception("Assets-Path-Type '$pathType' not found in config.");
            }
            $file = substr($file, strpos($file, '/')); //pathtype abschneiden
            $file = substr($file, 0, -1); //* abschneiden
            $path = $this->_config->path->$pathType.$file;
            if (!file_exists($path)) {
                throw new Vps_Exception("Path '$path' does not exist.");
            }
            $DirIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
            foreach ($DirIterator as $file) {
                if (!preg_match('#/\\.svn/#', $file->getPathname())
                    && (substr($file->getPathname(), -3) == '.js'
                        || substr($file->getPathname(), -4) == '.css')) {
                    $f = $file->getPathname();
                    $f = substr($f, strlen($this->_config->path->$pathType));
                    $f = $pathType . $f;
                    if (!$this->_hasFile($assetsType, $f)) {
                        $this->_files[$assetsType][] = $f;
                    }
                }
            }
        } else {
            if (!$this->_hasFile($assetsType, $file)) {
                $this->_files[$assetsType][] = $file;
            }
        }
    }
    public function getAssetPath($url)
    {
        if (file_exists($url)) return $url;
        $paths = $this->_config->path;

        $type = substr($url, 0, strpos($url, '/'));
        if (strpos($type, '-')!==false) {
            $type = substr($type, strpos($type, '-')+1); //section abschneiden
        }
        $url = substr($url, strpos($url, '/')+1);
        if (!isset($paths->$type)) {
            throw new Vps_Assets_NotFoundException("Assets-Path-Type '$type' for url '$url' not found in config.");
        }
        $p = $paths->$type;
        if (!file_exists($p.'/'.$url)) {
            throw new Vps_Assets_NotFoundException("Assets '$p/$url' not found");
        }
        return $p.'/'.$url;
    }


}
