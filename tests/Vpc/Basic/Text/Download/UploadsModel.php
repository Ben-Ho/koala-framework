<?php
class Vpc_Basic_Text_Download_UploadsModel extends Vps_Test_Uploads_Model
{
    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->createRow()->copyFile(VPS_PATH.'/images/information.png', 'foo', 'png', 'image/png');
    }
}
