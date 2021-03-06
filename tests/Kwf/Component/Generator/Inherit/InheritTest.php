<?php
class Kwf_Component_Generator_Inherit_InheritTest extends Kwc_TestAbstract
{
    public function setUp()
    {
        parent::setUp('Kwf_Component_Generator_Inherit_Root');
    }

    public function testInherit()
    {
        $c = $this->_root->getChildComponent('_static')->getChildComponents();
        $this->assertEquals(count($c), 1);
        $this->assertEquals(current($c)->componentId, 'root_static-box');

        $c = $this->_root->getChildComponent('_static')->getChildBoxes();
        $this->assertEquals(1, count($c));
        $this->assertEquals('root_static-box', current($c)->componentId);

        $cc = Kwc_Abstract::getIndirectChildComponentClasses('Kwf_Component_Generator_Inherit_Root',
                array('flags'=>array('foo'=>true)));
        $this->assertEquals(1, count($cc));
        $this->assertEquals('Kwf_Component_Generator_Inherit_Box', current($cc));

        $c = $this->_root->getChildComponent('_static');
        $cc = current($c->getChildComponents(array('hasEditComponents' => true)));
        $this->assertEquals('root_static-box', $cc->componentId);
        $c = $c->getRecursiveChildComponents(array('flags'=>array('foo'=>true)));
        $this->assertEquals(1, count($c));
        $this->assertEquals(current($c)->componentId, 'root_static-box-flag');

        $this->assertNotNull($this->_root->getComponentById('root_static-box'));
        $this->assertEquals($this->_root->getComponentById('root_static-box')->componentId, 'root_static-box');
        $this->assertEquals($this->_root->getComponentById('root_static-box-flag')->componentId, 'root_static-box-flag');
    }

    public function testInheritedGenerators()
    {
        $page = $this->_root->getComponentById('1');
        $this->assertNotNull($page);
        $gen = Kwf_Component_Generator_Abstract::getInstances($page);
        $this->assertEquals(count($gen), 2);

        $page = $this->_root->getChildComponent('_static');
        $this->assertNotNull($page);
        $gen = Kwf_Component_Generator_Abstract::getInstances($page);
        $this->assertEquals(count($gen), 1); //aus performancegründen hier nur einer, page generator wird durch spezial-code in Generator::getInstances nicht zurückgegeben
    }

}
