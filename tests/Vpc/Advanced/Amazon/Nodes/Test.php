<?php
/**
 * @group selenium
 * @group slow
 * @group Vpc_Amazon
 */
class Vpc_Advanced_Amazon_Nodes_Test extends Vps_Test_SeleniumTestCase
{
    private $_root;

    public function setUp()
    {
        if (!Vps_Registry::get('config')->amazon || !Vps_Registry::get('config')->amazon->key) {
            $this->markTestSkipped();
        }

        Vps_Component_Data_Root::setComponentClass('Vpc_Advanced_Amazon_Nodes_Root');
        $this->_root = Vps_Component_Data_Root::getInstance();
        parent::setUp();
    }

    public function testIt()
    {
        $this->openVpc('/amazon');
        $this->assertContainsText("css=.vpcAdvancedAmazonNodesTestComponent", "Php");
        $this->assertContainsText("css=.vpcAdvancedAmazonNodesTestComponent", "JavaScript");
        $this->clickAndWait('link=Php');

        $this->assertElementPresent('css=li.products a');
        $this->clickAndWait('css=li.products a');
        $this->assertElementPresent('link='.trlVps('order now at amazon'));
        $href = $this->getAttribute('link='.trlVps('order now at amazon').'@href');
        $this->assertEquals('http://www.amazon.de', substr($href, 0, 20));
        $this->assertContains('vps-21', $href);
    }
}
