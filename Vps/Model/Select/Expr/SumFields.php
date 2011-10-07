<?php
class Vps_Model_Select_Expr_SumFields implements Vps_Model_Select_Expr_Interface
{
    private $_fields;
    public function __construct(array $fields)
    {
        $this->_fields = $fields;
    }

    public function getFields()
    {
        return $this->_fields;
    }

    public function validate()
    {
        if (count($this->_fields) == 0) {
            throw new Vps_Exception("'".get_class($this)."' has to contain at least one field");
        }
    }

    public function getResultType()
    {
        return Vps_Model_Interface::TYPE_INTEGER;
    }

    public function toArray()
    {
        $fields = array();
        foreach ($this->_expressions as $i) {
            if ($i instanceof Vps_Model_Select_Expr_Interface) $i = $i->toArray();
            $fields[] = $i;
        }
        return array(
            'exprType' => str_replace('Vps_Model_Select_Expr_', '', get_class($this)),
            'fields' => $fields
        );
    }

    public static function fromArray(array $data)
    {
        $cls = 'Vps_Model_Select_Expr_'.$data['exprType'];
        $fields = array();
        foreach ($data['fields'] as $i) {
            $fields[] = Vps_Model_Select_Expr::fromArray($i);
        }
        return new $cls($fields);
    }
}
