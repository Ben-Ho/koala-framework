<?php
abstract class Vpc_Basic_LinkTag_Abstract_Component extends Vpc_Abstract
{
    public static function getSettings()
    {
        return array_merge(parent::getSettings(), array(
            'componentIcon' => new Vps_Asset('page_link')
        ));
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        $ret['data'] = $this->getData();
        return $ret;
    }
}
