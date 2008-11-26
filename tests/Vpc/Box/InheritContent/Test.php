<?php
/**
 * @group Vpc_Box_InheritContent
 */
class Vpc_Box_InheritContent_Test extends PHPUnit_Framework_TestCase
{
    private $_root;
    public function setUp()
    {
        Vps_Component_Data_Root::setComponentClass('Vpc_Box_InheritContent_Root');
        $this->_root = Vps_Component_Data_Root::getInstance();
    }

    public function testInheritContent()
    {
        $this->_assertInheritedChild('root-ic', 'root-ic-child');
        $this->_assertInheritedChild('root_page1-ic', 'root-ic-child');
        $this->_assertInheritedChild('root_page1_page2-ic', 'root_page1_page2-ic-child');
        $this->_assertInheritedChild('root_page1_page2_page3-ic', 'root_page1_page2-ic-child');
    }

    private function _assertInheritedChild($id, $expectedChildId)
    {
        $ic = $this->_root->getComponentById($id);
        $this->assertNotNull($ic);
        $vars = $ic->getComponent()->getTemplateVars();
        $this->assertEquals($vars['child']->componentId, $expectedChildId);
    }

    public function testEditComponents()
    {
        $c = $this->_root->getComponentById('root')
            ->getChildComponents(array('editComponent'=>true, 'page'=>false));
        $this->assertEquals(array_keys($c), array('root-ic'));

        $c = $this->_root->getComponentById('root_page1')
            ->getChildComponents(array('editComponent'=>true, 'page'=>false));
        $this->assertEquals(array_keys($c), array('root_page1-ic'));

        $c = $this->_root->getComponentById('root_page1_page2')
            ->getChildComponents(array('editComponent'=>true, 'page'=>false));
        $this->assertEquals(array_keys($c), array('root_page1_page2-ic'));
    }

}
