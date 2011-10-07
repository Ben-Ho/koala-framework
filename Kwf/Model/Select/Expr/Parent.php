<?php
class Kwf_Model_Select_Expr_Parent implements Kwf_Model_Select_Expr_Interface
{
    private $_parent;
    private $_field;

    public function __construct($parentRule, $field)
    {
        $this->_parent = $parentRule;
        $this->_field = $field;
    }

    public function getField()
    {
        return $this->_field;
    }

    public function getParent()
    {
        return $this->_parent;
    }

    public function validate()
    {
        if (!$this->_field) {
            throw new Kwf_Exception("No Field set for '"+get_class($this)+"'");
        }
        if (!is_string($this->_field)) {
            throw new Kwf_Exception("Field must be of type string in '"+get_class($this)+"'");
        }
        if (!$this->_parent) {
            throw new Kwf_Exception("No parent rule set for '"+get_class($this)+"'");
        }
    }

    public function getResultType()
    {
        return null;
    }
}
