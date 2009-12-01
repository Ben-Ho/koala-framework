<?php
class Vps_Benchmark_Counter_Memcache implements Vps_Benchmark_Counter_Interface
{
    public function getMemcache()
    {
        static $memcache;
        if (!isset($memcache)) {
            $memcache = new Memcache;
            $memcacheSettings = Vps_Registry::get('config')->server->memcache;
            $memcache->addServer($memcacheSettings->host, $memcacheSettings->port, true, 1, 1, 1);
        }
        return $memcache;
    }

    public function increment($name, $value=1)
    {
        $memcache = $this->getMemcache();
        static $prefix;
        if (!isset($prefix)) {
            $prefix = Zend_Registry::get('config')->application->id.'-'.
                                Vps_Setup::getConfigSection().'-bench-';
        }
        try {
            if (!($ret = $memcache->increment($prefix.$name, $value))) {
                $ret = $memcache->set($prefix.$name, $value, 0, 0);
            }
        } catch (ErrorException $e) {
            if ($e->getSeverity() == E_NOTICE) {
                $e = new Vps_Exception_Other($e);
                $e->logOrThrow();
                return false;
            }
            throw $e;
        }
        return $ret;
    }

    public function getValue($name)
    {
        $memcache = $this->getMemcache();
        static $prefix;
        if (!isset($prefix)) {
            $prefix = Zend_Registry::get('config')->application->id.'-'.
                                Vps_Setup::getConfigSection().'-bench-';
        }
        try {
            return $memcache->get($prefix.$name);
        } catch (ErrorException $e) {
            if ($e->getSeverity() == E_NOTICE) {
                $e = new Vps_Exception_Other($e);
                $e->logOrThrow();
                return false;
            }
            throw $e;
        }
    }

}
