<?php
/**
 * @group Update_Action
 * @group Update_Action_Rrd
 */
class Kwf_Update_Action_Rrd_AddDsTest extends Kwf_Update_Action_Rrd_AbstractTest
{
    public function testAddRrd()
    {
        $file = $this->_createTestFile();

        $action = new Kwf_Update_Action_Rrd_AddDs(array(
            'file' => $file,
            'name' => 'testxxx',
            'type' => 'ABSOLUTE',
            'minimalHeartbeat' => 120,
            'min' => 0,
            'max' => 1000,
            'backup'=>false,
            'silent' => true
        ));
        $action->preUpdate();
        $action->update();
        $action->postUpdate();

        $cmd = "rrdtool dump $file > $file.xml";
        $this->_systemCheckRet($cmd);

        $xml = simplexml_load_file($file.'.xml');

        $this->assertEquals(3, count($xml->ds));
        $this->assertEquals('testx', trim($xml->ds[0]->name));
        $this->assertEquals('testxx', trim($xml->ds[1]->name));
        $this->assertEquals('testxxx', trim($xml->ds[2]->name));

        unlink($file);
        unlink($file.'.xml');
    }
}
