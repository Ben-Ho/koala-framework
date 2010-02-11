<?php
/**
 * Controller der für eigene root-komponenten verwendet werden kann.
 * für selenium-tests.
 * Url: /vps/vpctest/Vpc_Basic_Text_Root/url
 */
class Vpc_TestController extends Vps_Controller_Action
{
    public function indexAction()
    {
        Zend_Registry::get('config')->debug->componentCache->disable = true;
        Zend_Registry::set('db', false);

        //FnF models setzen damit tests nicht in echte tabellen schreiben
        Vps_Component_Cache::getInstance()->setModel(new Vps_Component_Cache_CacheModel());
        Vps_Component_Cache::getInstance()->setMetaModel(new Vps_Component_Cache_CacheMetaModel());
        Vps_Component_Cache::getInstance()->setFieldsModel(new Vps_Component_Cache_CacheFieldsModel());

        Vps_Component_Data_Root::setComponentClass($this->_getParam('root'));
        $root = Vps_Component_Data_Root::getInstance();
        $root->setFilename('vps/vpctest/'.$this->_getParam('root'));

        $url = $this->_getParam('url');

        $urlParts = explode('/', $url);
        if (is_array($urlParts) && $urlParts[0] == 'media') {
            if (sizeof($urlParts) != 6) {
                throw new Vps_Exception_NotFound();
            }
            $class = $urlParts[1];
            $id = $urlParts[2];
            $type = $urlParts[3];
            $checksum = $urlParts[4];
            $filename = $urlParts[5];

            if ($checksum != Vps_Media::getChecksum($class, $id, $type, $filename)) {
                throw new Vps_Exception_AccessDenied('Access to file not allowed.');
            }
            Vps_Media_Output::output(Vps_Media::getOutput($class, $id, $type));
        }

        $domain = 'http://'.Zend_Registry::get('config')->server->domain;
        $data = $root->getPageByUrl($domain.'/'.$url);
        if (!$data) {
            throw new Vps_Exception_NotFound();
        }
        $root->setCurrentPage($data);
        $data->getComponent()->sendContent();

        $this->_helper->viewRenderer->setNoRender(true);
    }
}
