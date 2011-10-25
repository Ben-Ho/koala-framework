<?php
class Kwc_Trl_FormDynamic_Form_Paragraphs_TextField_TestModel extends Kwf_Model_FnF
{
    protected $_primaryKey = 'component_id';
    protected $_data = array(
        array('component_id'=>'root-master_test1-paragraphs-1', 'field_label'=>'Label'),
        array('component_id'=>'root-master_test1-paragraphs-2', 'field_label'=>'Required', 'required'=>true),
        array('component_id'=>'root-master_test1-paragraphs-3', 'field_label'=>'EMail', 'vtype'=>'email'),
        array('component_id'=>'root-master_test1-paragraphs-4', 'field_label'=>'Default', 'default_value'=>'Def'),
    );
}