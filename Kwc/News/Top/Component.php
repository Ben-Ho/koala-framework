<?php
abstract class Kwc_News_Top_Component extends Kwc_Directories_Top_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['componentName'] = trlKwf('News.Top');
        $ret['componentIcon'] = new Kwf_Asset('newspaper');
        $ret['generators']['child']['component']['view'] = 'Kwc_News_List_View_Component';
        return $ret;
    }
}