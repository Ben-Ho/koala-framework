<?php
class Kwf_Exception_AccessDenied extends Kwf_Exception_Abstract
{
    public function getHeader()
    {
        return 'HTTP/1.1 401 Access Denied';
    }

    public function getTemplate()
    {
        return 'Error401';
    }

    public function log()
    {
        $body = '';
        $body .= $this->_format('REQUEST_URI', isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '(none)');
        $body .= $this->_format('HTTP_REFERER', isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '(none)');
        $body .= $this->_format('Time', date('H:i:s'));
        $body .= $this->_format('_GET', print_r($_GET, true));
        $body .= $this->_format('_POST', print_r($_POST, true));

        $path = 'log/accessdenied/' . date('Y-m-d');

        $filename = date('H_i_s') . '_' . uniqid() . '.txt';

        return $this->_writeLog($path, $filename, $body);
    }
}
