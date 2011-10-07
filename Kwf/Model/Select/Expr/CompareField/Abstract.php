<?php
abstract class Kwf_Model_Select_Expr_CompareField_Abstract implements Kwf_Model_Select_Expr_Interface
{
    protected $_field;
    protected $_value;
    public function __construct($field, $value) {
        $this->_field = $field;
        $this->_value = $value;
    }

    public function getField()
    {
        return $this->_field;
    }

    public function getValue()
    {
        return $this->_value;
    }

	public function validate()
	{
		if (!$this->_field) {
			throw new Kwf_Exception("No Field-Value set for '"+get_class($this)+"'");
		}
	}

    public function getResultType()
    {
        return Kwf_Model_Interface::TYPE_BOOLEAN;
    }
}