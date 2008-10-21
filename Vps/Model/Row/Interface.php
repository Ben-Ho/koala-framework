<?php
interface Vps_Model_Row_Interface
{
    public function __isset($name);
    public function __unset($name);
    public function __get($name);
    public function __set($name, $value);
    public function save();
    public function delete();
    public function toArray();
    public function setSiblingRows(array $rows);
    public function getChildRows($rule, $select = array());
    public function createChildRow($rule, array $data = array());
    public function getParentRow($rule);
    public function getInternalId();
}
