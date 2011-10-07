<?php
class Kwf_Model_Select_Expr_Area implements Kwf_Model_Select_Expr_Interface
{
    protected $_field;
    protected $_latitude;
    protected $_longitude;
    protected $_radius;

    public function __construct($latitude, $longitude, $radius) {
        $this->_latitude = $latitude;
        $this->_longitude = $longitude;
        $this->_radius = $radius;
    }

    public function getLatitude()
    {
        return $this->_latitude;
    }

    public function getLongitude()
    {
        return $this->_longitude;
    }

    public function getRadius()
    {
        return $this->_radius;
    }

    public function validate()
    {
        if (!$this->_latitude || !$this->_longitude || !$this->_radius) {
            throw new Kwf_Exception("latitude, longitude und radius have to be set for '"+get_class($this)+"'");
        }
    }

    public function getResultType()
    {
        return Kwf_Model_Interface::TYPE_BOOLEAN;
    }
}