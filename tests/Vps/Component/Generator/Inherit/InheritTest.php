<?php
class Vps_Component_Generator_Inherit_InheritTest extends PHPUnit_Framework_TestCase
{
    private $_root;
    public function setUp()
    {
        Vps_Component_Data_Root::setComponentClass('Vps_Component_Generator_Inherit_Root');
        $this->_root = Vps_Component_Data_Root::getInstance();
    }

    public function testInherit()
    {
        $c = $this->_root->getChildComponent('_static')->getChildComponents();
        $this->assertEquals(count($c), 1);
        $this->assertEquals(current($c)->componentId, 'root_static-box');

        $c = $this->_root->getChildComponent('_static')->getChildBoxes();
        $this->assertEquals(count($c), 1);
        $this->assertEquals(current($c)->componentId, 'root_static-box');

        $cc = Vpc_Abstract::getIndirectChildComponentClasses('Vps_Component_Generator_Inherit_Root',
                array('flags'=>array('foo'=>true)));
        $this->assertEquals(1, count($cc));
        $this->assertEquals('Vps_Component_Generator_Inherit_Box', current($cc));
        
        $c = $this->_root->getChildComponent('_static');
        $c = $c->getRecursiveChildComponents(array('flags'=>array('foo'=>true)));
        $this->assertEquals(count($c), 1);
        $this->assertEquals(current($c)->componentId, 'root_static-box-flag');

        $this->assertNotNull($this->_root->getComponentById('root_static-box'));
        $this->assertEquals($this->_root->getComponentById('root_static-box')->componentId, 'root_static-box');
        $this->assertEquals($this->_root->getComponentById('root_static-box-flag')->componentId, 'root_static-box-flag');
    }

    public function testInheritedGenerators()
    {
        $page = $this->_root->getComponentById('1');
        $gen = Vps_Component_Generator_Abstract::getInstances($page);
        $this->assertEquals(count($gen), 2);

        $this->markTestIncomplete();
        $page = $this->_root->getChildComponent('_static');
        $gen = Vps_Component_Generator_Abstract::getInstances($page);
        $this->assertEquals(count($gen), 1);
    }

}
