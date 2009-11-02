<?php
abstract class Vps_Update
{
    protected $_tags = array();

    protected $_actions = array();
    protected $_revision;

    public function __construct($revision)
    {
        $this->_revision = (int)$revision;
        $this->_init();
    }

    public function getTags()
    {
        return $this->_tags;
    }

    public function getRevision()
    {
        return $this->_revision;
    }

    protected function _init()
    {
    }

    public function preUpdate()
    {
        $ret = array();
        foreach ($this->_actions as $a) {
            $res = $a->preUpdate();
            if ($res) {
                $ret[] = $res;
            }
        }
        return $ret;
    }

    public function postUpdate()
    {
        $ret = array();
        foreach ($this->_actions as $a) {
            $res = $a->postUpdate();
            if ($res) {
                $ret[] = $res;
            }
        }
        return $ret;
    }

    public function postClearCache()
    {
        $ret = array();
        foreach ($this->_actions as $a) {
            $res = $a->postClearCache();
            if ($res) {
                $ret[] = $res;
            }
        }
        return $ret;
    }
    public function checkSettings()
    {
        $ret = array();
        foreach ($this->_actions as $a) {
            $res = $a->checkSettings();
            if ($res) {
                $ret[] = $res;
            }
        }
        return $ret;
    }

    public function update()
    {
        $ret = array();
        foreach ($this->_actions as $a) {
            $res = $a->update();
            if ($res) {
                $ret[] = $res;
            }
        }
        return $ret;
    }

    public static function getUpdates($from, $to)
    {
        $ret = self::getVpcUpdates($from, $to);

        //web/Vps/ * /Update nach updates durchsuchen
        if (is_dir('./Vps')) {
            foreach (new DirectoryIterator('./Vps') as $d) {
                if ($d->isDir() && substr($d->__toString(), 0, 1) != '.'
                    && is_dir('./Vps/'.$d->__toString().'/Update')
                ) {
                    $u = self::getUpdatesForDir('Vps/'.$d->__toString(), $from, $to);
                    foreach ($u as $i) $i->_tags[] = 'web';
                    $ret = array_merge($ret, $u);
                }
            }
        }

        $u = self::getUpdatesForDir(VPS_PATH.'/Vps', $from, $to);
        $ret = array_merge($ret, $u);
        $u = self::getUpdatesForDir('./update', $from, $to);
        foreach ($u as $i) $i->_tags[] = 'web';
        $ret = array_merge($ret, $u);
        $ret = self::_sortByRevision($ret);
        return $ret;
    }

    public static function getVpcUpdates($from, $to)
    {
        $ret = array();
        $processed = array();
        foreach (Vps_Component_Abstract::getComponentClasses(false) as $class) {
            while ($class != '') {
                if (!in_array($class, $processed)) {
                    $processed[] = $class;
                    $curClass = $class;
                    if (substr($curClass, -10) == '_Component') {
                        $curClass = substr($curClass, 0, -10);
                    }
                    $file = str_replace('_', DIRECTORY_SEPARATOR, $curClass);
                    $ret = array_merge($ret, self::getUpdatesForDir($file, $from, $to));
                }
                $class = get_parent_class($class);
            }
        }
        return $ret;
    }

    public static function getUpdatesForDir($file, $from, $to)
    {
        $ret = array();
        foreach (explode(PATH_SEPARATOR, get_include_path()) as $dir) {
            if ($dir == '.') $dir = getcwd();
            if (substr($file, 0, strlen($dir)) == $dir) {
                $file = substr($file, strlen($dir)+1);
            }
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $path =  $path . '/Update';
                if (is_dir($path)) {
                    foreach (new DirectoryIterator($path) as $i) {
                        if (!$i->isFile()) continue;
                        $f = $i->__toString();
                        $fileType = substr($f, -4);
                        if ($fileType != '.php' && $fileType != '.sql') continue;
                        $f = substr($f, 0, -4);
                        if (!is_numeric($f)) continue;
                        $nr = (int)$f;
                        if ($nr >= $from && $nr < $to) {
                            if ($fileType == '.sql') {
                                $u = new Vps_Update_Sql($nr);
                                $u->sql = file_get_contents($i->getPathname());
                                if (preg_match("#\\#\\s*tags:(.*)#", $u->sql, $m)) {
                                    $u->_tags = explode(' ', trim($m[1]));
                                }
                                $u->_tags[] = 'db';
                                $ret[] = $u;
                            } else {
                                $n = '';
                                if ($file != './update') {
                                    $n = str_replace(DIRECTORY_SEPARATOR, '_', $file).'_';
                                }
                                $n .= 'Update_'.$nr;
                                if (is_instance_of($n, 'Vps_Update')) {
                                    $ret[] = new $n($nr);
                                }
                            }
                        }
                    }
                }
                $path = $path . '/Always';
                if (is_dir($path)) {
                    foreach (new DirectoryIterator($path) as $i) {
                        if (!$i->isFile()) continue;
                        $f = $i->__toString();
                        $fileType = substr($f, -4);
                        if ($fileType != '.php') continue;
                        $f = substr($f, 0, -4);
                        $n = str_replace(DIRECTORY_SEPARATOR, '_', $file).'_Update_Always_'.$f;
                        if (is_instance_of($n, 'Vps_Update')) {
                            $ret[] = new $n(null);
                        }
                    }
                }
                break;
            }
        }
        $ret = self::_sortByRevision($ret);
        return $ret;
    }

    private static function _sortByRevision($updates)
    {
        $revisions = array();
        foreach ($updates as $k=>$u) {
            $revisions[$k] = $u->getRevision();
            if (is_null($revisions[$k])) {
                $revisions[$k] = 99999999;
            }
        }
        asort($revisions, SORT_NUMERIC);
        $ret = array();
        foreach (array_keys($revisions) as $k) {
            $ret[] = $updates[$k];
        }
        return $ret;
    }
}
