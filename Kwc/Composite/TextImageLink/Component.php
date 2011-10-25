<?php
class Kwc_Composite_TextImageLink_Component extends Kwc_Abstract_Composite_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['componentName'] = trlKwf('Text Image Link');
        $ret['ownModel'] = 'Kwc_Composite_TextImageLink_Model';
        $ret['generators']['child']['component']['image'] = 'Kwc_Basic_Image_Component';
        $ret['generators']['child']['component']['link'] = 'Kwc_Basic_LinkTag_Component';
        $ret['default'] = array();
        return $ret;
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        $row = $this->_getRow();
        $ret['title'] = $row->title;
        $ret['teaser'] = $row->teaser;
        return $ret;
    }
}
