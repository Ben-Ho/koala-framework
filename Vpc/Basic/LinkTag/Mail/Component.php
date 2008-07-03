<?php
/**
 * @package Vpc
 * @subpackage Basic
 */
class Vpc_Basic_LinkTag_Mail_Component extends Vpc_Basic_LinkTag_Abstract_Component
{
    public static function getSettings()
    {
        $ret = array_merge(parent::getSettings(), array(
            'dataClass' => 'Vpc_Basic_LinkTag_Mail_Data',
            'tablename'     => 'Vpc_Basic_LinkTag_Mail_Model',
            'componentName' => 'Link.Mail',
            'default' => array()
        ));
        $ret['assets']['dep'][] = 'VpsMailDecode';
        return $ret;
    }
}
