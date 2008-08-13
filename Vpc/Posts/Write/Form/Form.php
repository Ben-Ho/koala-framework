<?php
class Vpc_Posts_Write_Form_Form extends Vpc_Abstract_Form
{
    protected function _init()
    {
        parent::_init();
        $this->add(new Vps_Form_Field_TextArea('content', trlVps('<strong>Create Post: </strong><br />Please enter the desired text. HTML is not allowed an will be filtered. Links like http://... or www.... will be linked automatically.')))
            ->setWidth(475)->setHeight(150);
        $this->add(new Vps_Form_Field_Panel('infotext'))
            ->setHtml(trlVps('Please write friendly in your posts. Every author is liable for the content of his/her posts. Offending posts will be deleted without a comment.'));
    }
}
