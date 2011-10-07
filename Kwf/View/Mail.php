<?php
class Kwf_View_Mail extends Kwf_View implements Kwf_View_MailInterface
{
    private $_images = array();
    protected $_attachImages = true;
    protected $_masterTemplate = null;

    public function setMasterTemplate($tpl)
    {
        $this->_masterTemplate = $tpl;
    }

    public function getMasterTemplate()
    {
        return $this->_masterTemplate;
    }

    public function addImage(Zend_Mime_Part $image)
    {
        $this->_images[] = $image;
    }

    public function getImages()
    {
        return $this->_images;
    }

    public function getAttachImages()
    {
        return $this->_attachImages;
    }

    public function setAttachImages($attachImages)
    {
        $this->_attachImages = $attachImages;
    }
}
