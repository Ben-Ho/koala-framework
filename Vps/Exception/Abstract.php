<?php
abstract class Vps_Exception_Abstract extends Exception
{
    private $_logFilename;

    public abstract function getHeader();

    public abstract function log();

    public function getTemplate()
    {
        return 'Error';
    }

    public static function isDebug()
    {
        return !Zend_Registry::get('config')->debug->error->log;
    }

    public function getException()
    {
        return $this;
    }

    protected function _writeLog($path, $filename, $content)
    {
        $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        if (self::isDebug()
            || substr($requestUri, -12) == '/favicon.ico'
            || substr($requestUri, -10) == '/robots.txt')
        {
            return false;
        }
        $this->_logFilename = $filename;
        try {
            if (!is_dir($path)) mkdir($path);
            $fp = fopen("$path/$filename", 'a');
            fwrite($fp, $content);
            fclose($fp);
        } catch(Exception $e) {
            mail('ufx@vivid-planet.com; ns@vivid-planet.com; mh@vivid-planet.com',
                'Error while trying to write error file',
                $e->__toString()."\n\n---------------------------\n\nOriginal Exception:\n\n".$content
                );
        }
        return true;
    }

    public function getLogFilename()
    {
        return $this->_logFilename;
    }

    protected function _format($part, $text)
    {
        return "** $part **\n$text\n-- $part --\n\n";
    }
}
