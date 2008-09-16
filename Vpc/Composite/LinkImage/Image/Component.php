<?php
class Vpc_Composite_LinkImage_Image_Component extends Vpc_Basic_Image_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['dimensions'] = array(150, 0, Vps_Media_Image::SCALE_BESTFIT);
        return $ret;
    }
}