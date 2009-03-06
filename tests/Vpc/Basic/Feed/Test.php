<?php
/**
 * @group Vpc_Basic_Feed
 **/
class Vpc_Basic_Feed_Test extends PHPUnit_Framework_TestCase
{
    private $_root;

    public function setUp()
    {
        Vps_Component_Data_Root::setComponentClass('Vpc_Basic_Feed_Root');
        $this->_root = Vps_Component_Data_Root::getInstance();
    }

    public function testFeed()
    {
        Vps_Component_Cache::getInstance()->setModel(new Vps_Component_Cache_CacheModel());
        Vps_Component_Cache::getInstance()->setMetaModel(new Vps_Component_Cache_CacheMetaModel());
        $feed = Vps_Component_Data_Root::getInstance()->getChildComponent('_feed');
        $xml = $feed->getComponent()->getXml();
        $rows = Vps_Component_Cache::getInstance()->getModel()->getRows();
        $row = $rows->current();

        // XML prüfen
        $this->assertEquals('<?xml', substr($xml, 0, 5));
        $this->assertTrue(strpos($xml, '<rss') !== false);
        $this->assertTrue(strpos($xml, 'testtitle') !== false);
        $this->assertTrue(strpos($xml, 'testdescription') !== false);
        $this->assertTrue(strpos($xml, 'testlink') !== false);

        // Cache-Eintrag prüfen
        $this->assertEquals($xml, $feed->getComponent()->getXml());
        $this->assertEquals(1, count($rows));
        $this->assertEquals($xml, $row->content);
        p(Vps_Component_Cache::getInstance()->getMetaModel()->getRows()->toArray());

        // Cache-Eintrag ändern um festzustellen, ob eh Cache verwendet wird
        $row->content = 'foo';
        $row->save();
        Vps_Component_Cache::getInstance()->emptyPreload();
        $this->assertEquals($row->content, $feed->getComponent()->getXml());

        // Cache löschen
        Vps_Component_Cache::getInstance()->clean(Vps_Component_Cache::CLEANING_MODE_ID, $feed->componentId);
        $xml = $feed->getComponent()->getXml();
        $this->assertEquals('<?xml', substr($xml, 0, 5));
        $this->assertTrue(strpos($xml, '<rss') !== false);
    }
}
