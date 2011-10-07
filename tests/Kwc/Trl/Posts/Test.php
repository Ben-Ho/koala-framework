<?php
/**
 * @group Kwc_Trl
 * @group Kwc_Trl_Posts
 * @group slow
 *
ansicht frontend:
/kwf/kwctest/Kwc_Trl_Posts_Root/de/test
/kwf/kwctest/Kwc_Trl_Posts_Root/en/test
 */
class Kwc_Trl_Posts_Test extends Kwc_TestAbstract
{
    public function setUp()
    {
        parent::setUp('Kwc_Trl_Posts_Root');
        $model = Kwf_Model_Abstract::getInstance('Kwc_Trl_Posts_Posts_Model')
            ->getProxyModel();
        $model->setData(array(
            array('id' => '1', 'component_id'=>'root-master_test', 'visible' => '1', 'create_time' => '2010-04-16 13:00:00', 'user_id' => NULL, 'content' => 'Inhalt de 1', 'data' => ''),
            array('id' => '2', 'component_id'=>'root-master_test', 'visible' => '1', 'create_time' => '2010-04-16 13:00:00', 'user_id' => NULL, 'content' => 'Inhalt de 2', 'data' => ''),
            array('id' => '3', 'component_id'=>'root-en_test-child', 'visible' => '1', 'create_time' => '2010-04-16 13:00:00', 'user_id' => NULL, 'content' => 'Content en 1', 'data' => ''),
            array('id' => '4', 'component_id'=>'root-en_test-child', 'visible' => '1', 'create_time' => '2010-04-16 13:00:00', 'user_id' => NULL, 'content' => 'Content en 2', 'data' => '')
        ));
    }

    public function testDe()
    {
        $c = $this->_root->getComponentById('root-master_test');
        $src = $c->render();
        $this->assertContains('Inhalt de 1', $src);
        $this->assertContains('Inhalt de 2', $src);
        $this->assertContains('schreiben', $src);
        $this->assertContains('/de/test/schreiben', $src);

        $c = $this->_root->getComponentById('root-en_test');
        $src = $c->render();
        $this->assertContains('Content en 1', $src);
        $this->assertContains('Content en 2', $src);
        $this->assertContains('Write', $src);
        $this->assertContains('/en/test/write', $src);
    }
}
