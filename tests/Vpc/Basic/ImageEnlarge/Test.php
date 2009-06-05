<?php
/**
 * @group Basic_ImageEnlarge
 */
class Vpc_Basic_ImageEnlarge_Test extends PHPUnit_Framework_TestCase
{
    private $_root;

    public function setUp()
    {
        Vps_Component_Data_Root::setComponentClass('Vpc_Basic_ImageEnlarge_Root');
        $this->_root = Vps_Component_Data_Root::getInstance();
        Vps_Component_Cache::getInstance()->setModel(new Vps_Component_Cache_CacheModel());
        Vps_Component_Cache::getInstance()->setMetaModel(new Vps_Component_Cache_CacheMetaModel());
        Vps_Component_Cache::getInstance()->setFieldsModel(new Vps_Component_Cache_CacheFieldsModel());
    }

    public function testWithoutSmallImageComponent()
    {
        $c = $this->_root->getComponentById('1800');
        $dim = $c->getComponent()->getImageDimensions();
        $this->assertEquals(10, $dim['width']);
        $this->assertEquals(10, $dim['height']);

        $url = explode('/', trim($c->getComponent()->getImageUrl(), '/'));
        $this->assertEquals('Vpc_Basic_ImageEnlarge_WithoutSmallImageComponent', $url[1]);
        $this->assertEquals('1800', $url[2]);
        $this->assertEquals('default', $url[3]);
    }

    public function testWithoutSmallImageComponentHtml()
    {
        $output = new Vps_Component_Output_NoCache();
        $html = $output->render($this->_root->getComponentById(1800));

        $doc = new DOMDocument();
        $doc->strictErrorChecking = FALSE;
        $doc->loadHTML($html);
        $xml = simplexml_import_dom($doc);

        $img = $xml->xpath("//img");
        $this->assertEquals(1, count($img));
        $this->assertEquals(10, (string)$img[0]['width']);
        $this->assertEquals(10, (string)$img[0]['height']);
        $this->assertTrue(!!preg_match('#^/media/([^/]+)/([^/]+)/([^/]+)#', (string)$img[0]['src'], $m));
        $o = call_user_func(array($m[1], 'getMediaOutput'), $m[2], $m[3], $m[1]);
        $this->assertEquals('image/png', $o['mimeType']);
        $im = new Imagick();
        $im->readImageBlob($o['contents']);
        $this->assertEquals(10, $im->getImageWidth());
        $this->assertEquals(10, $im->getImageHeight());
        $this->assertEquals(Vps_Media_Image::scale(Vps_Model_Abstract::getInstance('Vpc_Basic_ImageEnlarge_UploadsModel')->getUploadDir().'/1',
                                    array(10, 10, Vps_Media_Image::SCALE_DEFORM)), $o['contents']);

        $a = $xml->xpath("//a");
        $this->assertEquals(1, count($a));
        $this->assertEquals('enlarge_16_16', (string)$a[0]['rel']);
        $this->assertTrue(!!preg_match('#^/media/([^/]+)/([^/]+)/([^/]+)#', (string)$a[0]['href'], $m));
        $o = call_user_func(array($m[1], 'getMediaOutput'), $m[2], $m[3], $m[1]);
        $this->assertEquals('image/png', $o['mimeType']);
        $im = new Imagick();
        $im->readImageBlob($o['contents']);
        $this->assertEquals(16, $im->getImageWidth());
        $this->assertEquals(16, $im->getImageHeight());
        $this->assertEquals(Vps_Media_Image::scale(Vps_Model_Abstract::getInstance('Vpc_Basic_ImageEnlarge_UploadsModel')->getUploadDir().'/1',
                                    array(16, 16, Vps_Media_Image::SCALE_DEFORM)), $o['contents']);
    }

    public function testWithoutSmallImageUploaded()
    {
        $c = $this->_root->getComponentById('1801');
        $dim = $c->getComponent()->getImageDimensions();
        $this->assertEquals(10, $dim['width']);
        $this->assertEquals(10, $dim['height']);

        $url = explode('/', trim($c->getComponent()->getImageUrl(), '/'));
        $this->assertEquals('Vpc_Basic_ImageEnlarge_TestComponent', $url[1]);
        $this->assertEquals('1801', $url[2]);
        $this->assertEquals('default', $url[3]);
    }

    public function testWithoutSmallImageUploadedHtml()
    {
        $output = new Vps_Component_Output_NoCache();
        $html = $output->render($this->_root->getComponentById(1801));

        $doc = new DOMDocument();
        $doc->strictErrorChecking = FALSE;
        $doc->loadHTML($html);
        $xml = simplexml_import_dom($doc);

        $img = $xml->xpath("//img");
        $this->assertEquals(1, count($img));
        $this->assertEquals(10, (string)$img[0]['width']);
        $this->assertEquals(10, (string)$img[0]['height']);
        $this->assertTrue(!!preg_match('#^/media/([^/]+)/([^/]+)/([^/]+)#', (string)$img[0]['src'], $m));
        $o = call_user_func(array($m[1], 'getMediaOutput'), $m[2], $m[3], $m[1]);
        $this->assertEquals('image/png', $o['mimeType']);
        $im = new Imagick();
        $im->readImageBlob($o['contents']);
        $this->assertEquals(10, $im->getImageWidth());
        $this->assertEquals(10, $im->getImageHeight());
        $this->assertEquals(Vps_Media_Image::scale(Vps_Model_Abstract::getInstance('Vpc_Basic_ImageEnlarge_UploadsModel')->getUploadDir().'/1',
                                    array(10, 10, Vps_Media_Image::SCALE_DEFORM)), $o['contents']);

        $a = $xml->xpath("//a");
        $this->assertEquals(1, count($a));
        $this->assertEquals('enlarge_16_16', (string)$a[0]['rel']);
        $this->assertTrue(!!preg_match('#^/media/([^/]+)/([^/]+)/([^/]+)#', (string)$a[0]['href'], $m));
        $o = call_user_func(array($m[1], 'getMediaOutput'), $m[2], $m[3], $m[1]);
        $this->assertEquals('image/png', $o['mimeType']);
        $im = new Imagick();
        $im->readImageBlob($o['contents']);
        $this->assertEquals(16, $im->getImageWidth());
        $this->assertEquals(16, $im->getImageHeight());
        $this->assertEquals(Vps_Media_Image::scale(Vps_Model_Abstract::getInstance('Vpc_Basic_ImageEnlarge_UploadsModel')->getUploadDir().'/1',
                                    array(16, 16, Vps_Media_Image::SCALE_DEFORM)), $o['contents']);
    }

    public function testWithSmallImageUploaded()
    {
        $c = $this->_root->getComponentById('1802');
        $dim = $c->getComponent()->getImageDimensions();
        $this->assertEquals(10, $dim['width']);
        $this->assertEquals(10, $dim['height']);
        $this->assertEquals('1802-linkTag', $c->getComponent()->getImageRow()->component_id);

        $url = explode('/', trim($c->getComponent()->getImageUrl(), '/'));
        $this->assertEquals('Vpc_Basic_ImageEnlarge_TestComponent', $url[1]);
        $this->assertEquals('1802', $url[2]);
        $this->assertEquals('default', $url[3]);

        $c = $this->_root->getComponentById('1802-linkTag');
        $dim = $c->getComponent()->getImageDimensions();
        $this->assertEquals(210, $dim['width']);
        $this->assertEquals(70, $dim['height']);
        $this->assertEquals('1802', $c->getComponent()->getImageRow()->component_id);
    }

    public function testWithSmallImageUploadedHtml()
    {
        $output = new Vps_Component_Output_NoCache();
        $html = $output->render($this->_root->getComponentById(1802));

        $doc = new DOMDocument();
        $doc->strictErrorChecking = FALSE;
        $doc->loadHTML($html);
        $xml = simplexml_import_dom($doc);

        $img = $xml->xpath("//img");
        $this->assertEquals(1, count($img));
        $this->assertEquals(10, (string)$img[0]['width']);
        $this->assertEquals(10, (string)$img[0]['height']);
        $this->assertTrue(!!preg_match('#^/media/([^/]+)/([^/]+)/([^/]+)#', (string)$img[0]['src'], $m));
        $o = call_user_func(array($m[1], 'getMediaOutput'), $m[2], $m[3], $m[1]);
        $this->assertEquals('image/png', $o['mimeType']);
        $im = new Imagick();
        $im->readImageBlob($o['contents']);
        $this->assertEquals(10, $im->getImageWidth());
        $this->assertEquals(10, $im->getImageHeight());
        $this->assertEquals(Vps_Media_Image::scale(Vps_Model_Abstract::getInstance('Vpc_Basic_ImageEnlarge_UploadsModel')->getUploadDir().'/1',
                                    array(10, 10, Vps_Media_Image::SCALE_DEFORM)), $o['contents']);

        $a = $xml->xpath("//a");
        $this->assertEquals(1, count($a));
        $this->assertEquals('enlarge_210_70', (string)$a[0]['rel']);
        $this->assertTrue(!!preg_match('#^/media/([^/]+)/([^/]+)/([^/]+)#', (string)$a[0]['href'], $m));
        $o = call_user_func(array($m[1], 'getMediaOutput'), $m[2], $m[3], $m[1]);
        $this->assertEquals('image/gif', $o['mimeType']);
        $im = new Imagick();
        $im->readImageBlob($o['contents']);
        $this->assertEquals(210, $im->getImageWidth());
        $this->assertEquals(70, $im->getImageHeight());
        $this->assertEquals(Vps_Media_Image::scale(Vps_Model_Abstract::getInstance('Vpc_Basic_ImageEnlarge_UploadsModel')->getUploadDir().'/2',
                                    array(270, 70, Vps_Media_Image::SCALE_BESTFIT)), $o['contents']);
    }

    public function testWithOriginalHtml()
    {
        $output = new Vps_Component_Output_NoCache();
        $html = $output->render($this->_root->getComponentById(1803));

        $doc = new DOMDocument();
        $doc->strictErrorChecking = FALSE;
        $doc->loadHTML($html);
        $xml = simplexml_import_dom($doc);

        $opt = $xml->xpath("//input");
        $this->assertEquals(1, count($opt));
        $opt = Zend_Json::decode((string)$opt[0]['value']);
        $this->assertTrue(!!preg_match('#^/media/([^/]+)/([^/]+)/([^/]+)#', $opt['fullSizeUrl'], $m));
        $o = call_user_func(array($m[1], 'getMediaOutput'), $m[2], $m[3], $m[1]);
        $this->assertEquals('image/png', $o['mimeType']);
        $im = new Imagick();
        $this->assertEquals(Vps_Model_Abstract::getInstance('Vpc_Basic_ImageEnlarge_UploadsModel')->getUploadDir().'/1', $o['file']);
    }
}
