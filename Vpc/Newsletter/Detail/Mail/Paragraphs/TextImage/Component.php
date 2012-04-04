<?php
class Vpc_Newsletter_Detail_Mail_Paragraphs_TextImage_Component extends Vpc_TextImage_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['child']['component']['text'] =
            'Vpc_Newsletter_Detail_Mail_Paragraphs_TextImage_Text_Component';
        $ret['generators']['child']['component']['image'] =
            'Vpc_Newsletter_Detail_Mail_Paragraphs_TextImage_Image_Component';
        return $ret;
    }
}
