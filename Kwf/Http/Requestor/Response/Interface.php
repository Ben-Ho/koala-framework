<?php
interface Kwf_Http_Requestor_Response_Interface
{
    public function getBody();
    public function getStatusCode();
    public function getContentType();
    public function getHeader($h);
    public function __toString();
}
