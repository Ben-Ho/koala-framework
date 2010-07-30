<?php
class Vpc_Columns_TestComponent extends Vpc_Columns_Component
{
    public static function getSettings($parentComponentClass)
    {
        $ret = parent::getSettings($parentComponentClass);
        $ret['generators']['child']['model'] = 'Vpc_Columns_TestColumnsModel';
        $ret['generators']['child']['component'] = 'Vpc_Columns_TestComponent_Column';
        $ret['ownModel'] = 'Vpc_Columns_TestModel';
        return $ret;
    }


}
