<?php
/**
 * @group Basic_Image
 */
class Vpc_Basic_Image_Test extends PHPUnit_Framework_TestCase
{
    private $_root;

    public function setUp()
    {
        Vps_Component_Data_Root::setComponentClass('Vpc_Basic_Image_Root');
        $this->_root = Vps_Component_Data_Root::getInstance();
    }

    public function testUrl()
    {
        $c = $this->_root->getComponentById('1600');
        $url = $c->getComponent()->getImageUrl();
        $url = explode('/', trim($url, '/'));
        $this->assertEquals('Vpc_Basic_Image_FixDimensionComponent', $url[1]);
        $this->assertEquals('1600', $url[2]);
        $this->assertEquals('default', $url[3]);
        $this->assertEquals('foo.png', $url[5]);
    }

    public function testUrlWithOwnFilename()
    {
        $c = $this->_root->getComponentById('1601');
        $url = $c->getComponent()->getImageUrl();
        $url = explode('/', trim($url, '/'));
        $this->assertEquals('myname.png', $url[5]);
    }

    public function testFixDimension()
    {
        $c = $this->_root->getComponentById('1600');
        $this->assertTrue($c->hasContent());

        $this->assertEquals(array('width'=>100, 'height'=>100, 'scale'=>Vps_Media_Image::SCALE_DEFORM),
            $c->getComponent()->getImageDimensions());
    }

    public function testGetMediaOutput()
    {
        $o = Vpc_Basic_Image_Component::getMediaOutput('1600', 'default', 'Vpc_Basic_Image_FixDimensionComponent');
        $this->assertEquals('image/png', $o['mimeType']);
        $im = new Imagick();
        $im->readImageBlob($o['contents']);
        $this->assertEquals(100, $im->getImageWidth());
        $this->assertEquals(100, $im->getImageHeight());
        $this->assertContains(Vps_Model_Abstract::getInstance('Vpc_Basic_Image_UploadsModel')->getUploadDir().'/1', $o['mtimeFiles']);
        $this->assertContains(VPS_PATH.'/Vpc/Basic/Image/Component.php', $o['mtimeFiles']);
        $this->assertContains(VPS_PATH.'/tests/Vpc/Basic/Image/FixDimensionComponent.php', $o['mtimeFiles']);
    }

    public function testHtml()
    {
        $output = new Vps_Component_Output_NoCache();
        $html = $output->render($this->_root->getComponentById(1600));
        $this->assertEquals('<div class="vpcBasicImageFixDimensionComponent">'.
            '<img src="/media/Vpc_Basic_Image_FixDimensionComponent/1600/default/74d187822e02d6b7e96b53938519c028/foo.png" width="100" height="100" alt="" class="" />'.
            '</div>', $html);
    }

    public function testEmpty()
    {
        $c = $this->_root->getComponentById('1602');
        $this->assertFalse($c->hasContent());

        $output = new Vps_Component_Output_NoCache();
        $html = $output->render($c);
        $this->assertEquals('<div class="vpcBasicImageFixDimensionComponent">'.
            '</div>', $html);
    }

    public function testDimensionSetByRow()
    {
        $c = $this->_root->getComponentById('1603');

        $this->assertEquals(array('width'=>10, 'height'=>10, 'scale'=>Vps_Media_Image::SCALE_DEFORM),
            $c->getComponent()->getImageDimensions());
    }

    public function testEmptyImagePlaceholder()
    {
        $c = $this->_root->getComponentById('1604');
        $this->assertTrue($c->hasContent());
        $url = $c->getComponent()->getImageUrl();
        $this->assertNotNull($url);

        $this->assertEquals(array('width'=>16, 'height'=>16, 'scale'=>Vps_Media_Image::SCALE_DEFORM),
            $c->getComponent()->getImageDimensions());

        $o = Vpc_Basic_Image_Component::getMediaOutput($c->componentId, 'default', $c->componentClass);
        $this->assertNotNull($o);
        $this->assertEquals('image/png', $o['mimeType']);
        $im = new Imagick();
        $im->readImageBlob($o['contents']);
        $this->assertEquals(16, $im->getImageWidth());
        $this->assertEquals(16, $im->getImageHeight());
        $this->assertEquals(file_get_contents(dirname(__FILE__).'/EmptyImageComponent/empty.png'), $o['contents']);
    }

    public function testParentImage()
    {
        $c = $this->_root->getComponentById('1605-child');
        $this->assertTrue($c->hasContent());
        $url = $c->getComponent()->getImageUrl();
        $this->assertNotNull($url);
        $url = explode('/', trim($url, '/'));
        $class = $url[1];
        $id = $url[2];
        $type = $url[3];

        $o = Vpc_Basic_Image_Component::getMediaOutput($id, $type, $class);
        $this->assertNotNull($o);
        $this->assertEquals('image/png', $o['mimeType']);
        $im = new Imagick();
        $im->readImageBlob($o['contents']);
        $this->assertEquals(16, $im->getImageWidth());
        $this->assertEquals(16, $im->getImageHeight());
    }

    public function testClearOutputCache()
    {
        $cache = $this->getMock('Vps_Component_Cache', array('remove'), array(), '', false);
        Vps_Component_Cache::setInstance($cache);
        Vps_Media::getOutputCache()->clean();

        Vpc_Basic_Image_FixDimensionComponent::$getMediaOutputCalled = 0;

        Vps_Media::getOutput('Vpc_Basic_Image_FixDimensionComponent', '1600', 'default');
        $this->assertEquals(1, Vpc_Basic_Image_FixDimensionComponent::$getMediaOutputCalled);

        Vps_Media::getOutput('Vpc_Basic_Image_FixDimensionComponent', '1600', 'default');
        $this->assertEquals(1, Vpc_Basic_Image_FixDimensionComponent::$getMediaOutputCalled);

        Vps_Media::getOutputCache()->clean();
        Vps_Media::getOutput('Vpc_Basic_Image_FixDimensionComponent', '1600', 'default');
        $this->assertEquals(2, Vpc_Basic_Image_FixDimensionComponent::$getMediaOutputCalled);

        $c = $this->_root->getComponentById('1600');
        $c->getComponent()->getImageRow()->save();
        Vps_Component_RowObserver::getInstance()->process(false);
        Vps_Media::getOutput('Vpc_Basic_Image_FixDimensionComponent', '1600', 'default');
        $this->assertEquals(3, Vpc_Basic_Image_FixDimensionComponent::$getMediaOutputCalled);

    }
}
