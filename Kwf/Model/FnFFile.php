<?php
/**
 * @package Model
 */
class Kwf_Model_FnFFile extends Kwf_Model_FnF
{
    protected $_fileName;

    public function __construct(array $config = array())
    {
        if (isset($config['fileName'])) {
            $this->_fileName = 'temp/fnf-file-'.$config['fileName'];
        }
        if (!$this->_fileName && $this->_uniqueIdentifier) {
            $this->_fileName = 'temp/fnf-file-'.$this->_uniqueIdentifier;
        }
        if (!$this->_fileName && isset($config['uniqueIdentifier'])) {
            $this->_fileName = 'temp/fnf-file-'.$config['uniqueIdentifier'];
        }
        if (!$this->_fileName) {
            if (get_class($this) == 'Kwf_Model_FnFFile') {
                throw new Kwf_Exception("Inhert from Kwf_Model_FnFFile or set an filename/uniqueIdentifier");
            }
            $this->_fileName = 'temp/fnf-file-'.get_class($this);
        }
        parent::__construct($config);
    }

    protected function _dataModified()
    {
        file_put_contents($this->_fileName, serialize($this->_data));
    }

    public function getData()
    {
        clearstatcache();
        if (file_exists($this->_fileName)) {
            $this->_data = unserialize(file_get_contents($this->_fileName));
        }
        if (!$this->_data) $this->_data = array();
        foreach ($this->_rows as $key=>$row) {
            if (!isset($this->_data[$key])) {
                unset($this->_rows[$key]);
            } else {
                $row->setData($this->_data[$key]);
            }
        }
        return $this->_data;
    }
}
