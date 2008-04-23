<?php
class Vpc_Basic_LinkTag_Mail_Form extends Vpc_Abstract_Form
{
    public function __construct($name, $class, $id = null)
    {
        parent::__construct($name, $class, $id);

        $this->add(new Vps_Form_Field_TextField('mail', 'E-Mail Address'))
            ->setWidth(300)
            ->setAllowBlank(false);
        $this->add(new Vps_Form_Field_TextField('subject', 'Predefined Subject for Mail'))
            ->setWidth(300);
        $this->add(new Vps_Form_Field_TextArea('text', 'Predefined Text for Mail'))
            ->setWidth(300)
            ->setHeight(200);
    }
}
