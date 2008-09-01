<?php
class Vps_Component_Generator_Recursive_RecursiveTest extends PHPUnit_Framework_TestCase
{
    private $_root;
    public function setUp()
    {
        Vps_Component_Data_Root::setComponentClass('Vps_Component_Generator_Recursive_Root');
        $this->_root = Vps_Component_Data_Root::getInstance();
    }

    public function testFlag()
    {
        $this->assertEquals(count(Vpc_Abstract::getIndirectChildComponentClasses('Vps_Component_Generator_Recursive_Static',
            array('flags'=>array('foo'=>true)))), 1);

        $this->_assertRecursiveIds($this->_root->getChildComponent('_static'),
                                    array('flags'=>array('foo'=>true)),
                                    array('root_static-static2-flag'));

    }

    private function _assertRecursiveIds($component, $select, $ids)
    {
        $select = new Vps_Component_Select($select);
        $initailSelect = clone $select;

        $c = $component->getRecursiveChildComponents($select);
        $foundIds = array();
        foreach ($c as $i) {
            $foundIds[] = $i->componentId;
        }
        $this->assertEquals($foundIds, $ids);
        $this->assertEquals($initailSelect, $select); //check if select was modified
    }

    public function testPages()
    {
        $c = $this->_root->getChildPages();
        $this->assertEquals(count($c), 1);
        $this->assertEquals(current($c)->componentId, 'root_static');

        $c = current($c)->getChildPages();
        $this->assertEquals(count($c), 1);
        $this->assertEquals(current($c)->componentId, 'root_static-static2_page');
    }

    public function testGetComponentByClass()
    {
        $generator = Vps_Component_Generator_Abstract::getInstance('Vps_Component_Generator_Recursive_Root', 'static');
        $this->assertEquals(1, count($generator->getChildData(null, array('componentClass'=>'Vps_Component_Generator_Recursive_Static'))));
        $this->assertNotNull($this->_root->getComponentByClass('Vps_Component_Generator_Recursive_Static'));
    }

}
