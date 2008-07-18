<?php
require_once 'Zend/Registry.php';
class Vps_Registry extends Zend_Registry
{
    public function offsetGet($index)
    {
        if ($index == 'db' && !parent::offsetExists($index)) {
            $v = Vps_Setup::createDb();
            $this->offsetSet('db', $v);
            return $v;
        } else if ($index == 'dao' && !parent::offsetExists($index)) {
            $v = Vps_Setup::createDao();
            $this->offsetSet('dao', $v);
            return $v;
        } else if ($index == 'config' && !parent::offsetExists($index)) {
            require_once 'Vps/Config/Cache.php';
            $cache = new Vps_Config_Cache;
            $cacheId = Vps_Setup::getConfigSection();
            require_once 'Zend/Config/Ini.php';
            if(!$v = $cache->load($cacheId)) {
                $v = Vps_Setup::createConfig();
                $cache->save($v, $cacheId);
            }
            $this->offsetSet('config', $v);
            return $v;
        } else if ($index == 'acl' && !parent::offsetExists($index)) {
            $v = new Vps_Acl();
            $this->offsetSet('acl', $v);
            return $v;
        } else if ($index == 'userModel' && !parent::offsetExists($index)) {
            $v = new Vps_Model_User_Users();
            $this->offsetSet('userModel', $v);
            return $v;
        } else if ($index == 'trl' && !parent::offsetExists($index)) {
            $v = new Vps_Trl();
            $this->offsetSet('trl', $v);
            return $v;
        } else if ($index == 'hlp' && !parent::offsetExists($index)) {
            $v = new Vps_Hlp();
            $this->offsetSet('hlp', $v);
            return $v;
        }
        return parent::offsetGet($index);
    }

    public function offsetExists($index)
    {
        if (in_array($index, array('db', 'dao', 'config', 'acl', 'userModel', 'trl', 'hlp'))) {
            return true;
        }
        return parent::offsetExists($index);
    }
}
