<?php
/**
 * @group Mail
 * @group Mail_Template
 */
class Kwf_Mail_Template_Test extends Kwc_TestAbstract
{
    public function setUp()
    {
        parent::setUp('Kwf_Mail_Template_Root');
    }

    public function testMailComponent()
    {
        $path = realpath(dirname(__FILE__));

        $c = $this->_root->getChildComponent('-both');
        $m = new Kwf_Mail_Template($c);
        $this->assertEquals($path.'/Both/Component.txt.tpl', realpath($m->getTxtTemplate()));
        $this->assertEquals($path.'/Both/Component.html.tpl', realpath($m->getHtmlTemplate()));

        $c = $this->_root->getChildComponent('-both');
        $m = new Kwf_Mail_Template($c->getComponent());
        $this->assertEquals($path.'/Both/Component.txt.tpl', realpath($m->getTxtTemplate()));
        $this->assertEquals($path.'/Both/Component.html.tpl', realpath($m->getHtmlTemplate()));

        $c = $this->_root->getChildComponent('-both');
        $classname = get_class($c->getComponent());
        $m = new Kwf_Mail_Template($classname);
        $this->assertEquals($path.'/Both/Component.txt.tpl', realpath($m->getTxtTemplate()));
        $this->assertEquals($path.'/Both/Component.html.tpl', realpath($m->getHtmlTemplate()));


        $c = $this->_root->getChildComponent('-txtonly');
        $m = new Kwf_Mail_Template($c);
        $this->assertEquals($path.'/TxtOnly/Component.txt.tpl', realpath($m->getTxtTemplate()));
        $this->assertEquals(null, $m->getHtmlTemplate());
    }

    public function testMailString()
    {
        $m = new Kwf_Mail_Template('UserActivation');
        $this->assertEquals('mails/UserActivation.txt.tpl', $m->getTxtTemplate());
        $this->assertEquals('mails/UserActivation.html.tpl', $m->getHtmlTemplate());
    }

    public function testMailSending()
    {
        $mockMail = $this->getMock('Kwf_Mail', array('send'));

        $c = $this->_root->getChildComponent('-both');
        $m = new Kwf_Mail_Template($c);
        $m->getView()->addScriptPath('.');
        $m->setMail($mockMail);
        $m->subject = 'a special subject';
        $m->foo = 'bar';
        $m->send();

        $this->assertEquals('a special subject', $m->getMail()->getSubject());
        $this->assertEquals('The foo variable is: bar', $m->getMail()->getBodyText(true));
        $this->assertEquals('The foo variable is:<br />bar', $m->getMail()->getBodyHtml(true));
    }

    /**
     * @expectedException Kwf_Exception
     */
    public function testNoAbsolutePath()
    {
        $m = new Kwf_Mail_Template(dirname(__FILE__));
    }

    /**
     * @expectedException Kwf_Exception
     */
    public function testNotExistingFileComponentData()
    {
        $c = $this->_root->getChildComponent('-notpl');
        $m = new Kwf_Mail_Template($c);
    }

    /**
     * @expectedException Kwf_Exception
     */
    public function testNotExistingTxt()
    {
        $c = $this->_root->getChildComponent('-htmlonly');
        $m = new Kwf_Mail_Template($c);
    }

    /**
     * @expectedException Kwf_Exception
     */
    public function testNotExistingFile()
    {
        new Kwf_Mail_Template('DoesNotExist');
    }
}
