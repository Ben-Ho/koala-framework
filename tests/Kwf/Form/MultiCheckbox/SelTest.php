<?php
/**
 * @group slow
 * @group selenium
 * @group Kwf_Form_MultiCheckbox
 */
class Kwf_Form_MultiCheckbox_SelTest extends Kwf_Test_SeleniumTestCase
{
    public function test()
    {
        $this->open('/kwf/test/kwf_form_multi-checkbox_test');
        $this->waitForConnections();
    }

}
