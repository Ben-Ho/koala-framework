<?php
class Vps_Exception_NotFound extends Vps_Exception_Abstract
{
    public function getHeader()
    {
        return 'HTTP/1.1 404 Not Found';
    }

    public function getTemplate()
    {
        return 'Error404';
    }

    public function log()
    {
        $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '(none)';
        $ignore = array(
            '/favicon.ico',
            '/robots.txt',
        );
        if (in_array($requestUri, $ignore)) {
            return false;
        }

        $body = '';
        $body .= $this->_format('REQUEST_URI', isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '(none)');
        $body .= $this->_format('HTTP_REFERER', isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '(none)');
        $body .= $this->_format('Time', date('H:i:s'));

        $path = 'log/notfound/' . date('Y-m-d');

        $filename = date('H_i_s') . '_' . uniqid() . '.txt';

        return $this->_writeLog($path, $filename, $body);
    }

    public function render($ignoreCli = false)
    {
        if (isset($_SERVER['REQUEST_URI']) && Vps_Registry::get('db')) {
            $target = Vps_Model_Abstract::getInstance('Vps_Util_Model_Redirects')
                ->findRedirectUrl('path', $_SERVER['REQUEST_URI']);
            if ($target) {
                header('Location: '.$target, true, 301);
                exit;
            }
        }
        parent::render($ignoreCli);
    }
}
