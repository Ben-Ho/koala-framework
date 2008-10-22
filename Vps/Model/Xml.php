<?php
class Vps_Model_Xml extends Vps_Model_Data_Abstract
{
    protected $_filepath;
    protected $_xpath;
    protected $_topNode;
    protected $_xmlContent;
    private $_simpleXml;

    protected $_rowClass = 'Vps_Model_Xml_Row';
    protected $_rowSetClass = 'Vps_Model_Xml_Rowset';

    public function __construct(array $config = array())
    {
        if (isset($config['filepath'])) $this->_filepath = $config['filepath'];
        if (isset($config['xmlContent'])) $this->_xmlContent = $config['xmlContent'];
        if (isset($config['xpath'])) $this->_xpath = $config['xpath'];
        if (isset($config['topNode'])) $this->_topNode = $config['topNode'];
        parent::__construct($config);
    }

    public function getRows($where=null, $order=null, $limit=null, $start=null)
    {

        $data = $this->getData();
        if (!is_object($where)) {
            $select = $this->select($where, $order, $limit, $start);
        } else {
            $select = $where;
        }
        $dataKeys = $this->_selectDataKeys($select, $data);
        return new $this->_rowsetClass(array(
            'model' => $this,
            'rowClass' => $this->_rowClass,
            'dataKeys' => $dataKeys
        ));
    }

    public function getData ()
    {
        if ($this->_data) {
            return $this->_data;
        } else {
	        $data = array();
	        foreach ($this->_getElements() as $key=>$element) {
	            $data[$key] = (array)$element;
	        }
	        $this->_data = $data;
	        return $this->_data;

        }
    }

    public function getRowByDataKey($key)
    {
        if (!isset($this->_rows[$key])) {
            $elements = $this->_getElements();
            $data = (array)$elements[$key];
            $this->_rows[$key] = new $this->_rowClass(array(
                'data' => $data,
                'model' => $this
            ));
        }
        return $this->_rows[$key];
    }

    public function update(Vps_Model_Row_Interface $row, $rowData)
    {
        $id = $row->{$this->getPrimaryKey()};
        $simpleXml = $this->_getSimpleXml();
        foreach ($this->_getElements() as $f) {
            if ($f->{$this->getPrimaryKey()} == $id) {
                foreach ($rowData as $k=>$i) {
                    $f->$k = $i;
                }
            }
        }
        if ($this->_filepath) {
            file_put_contents($this->_filepath, $this->_asPrettyXML($simpleXml->asXML()));
        }
        return $row->{$this->getPrimaryKey()};
    }

    public function insert(Vps_Model_Row_Interface $row, $rowData)
    {
        //performancemäßig noch nicht sehr gut
        $data = $this->getData();
        if (!$this->getPrimaryKey()) {
            throw new Vps_Exception("No Insertion without a primary key");
        }
        if ($this->_idExists($rowData[$this->getPrimaryKey()])){
            throw new Vps_Exception("Id is already used");
        }


        $simpleXml = $this->_getSimpleXml();
        $toAddXml = $this->_getRootElement();

        $node = $toAddXml->addChild($this->_topNode);

        $id = null;

        if (!array_key_exists($this->getPrimaryKey(), $rowData)) {
            throw new Vps_Exception("No Id was set, inserting impossible");
        }
        foreach ($rowData as $k=>$i) {
           if ($k == $this->getPrimaryKey()) {
               if (!$i) {
                   $i = $this->_getNewId();
               }
               $id = $i;
           }
           if (is_array($i)) {
               throw  new Vps_Exception("No arguments allowed in a Xml Node");
           }
           $node->addChild($k, $i);
        }

        if ($this->_filepath) {
            file_put_contents($this->_filepath, $this->_asPrettyXML($simpleXml->asXML()));
        }

        $row->{$this->getPrimaryKey()} = $id;
        $rowData[$this->getPrimaryKey()] = $id;
        $key = $this->_generateKey();
        $this->_data[$key] = $rowData;
        $this->_rows[$key] = $row;
        return $id;
    }

    private function _generateKey()
    {
        $rand = rand();
        while (array_key_exists($rand, $this->_data)) {
            $rand = rand();
        }
        return $rand;
    }

    public function delete(Vps_Model_Row_Interface $row)
    {
        $id = $row->{$this->getPrimaryKey()};
        $xml = $this->_getRootElement();

        foreach ($this->_rows as $k=>$i) {
            if ($row === $i) {
                unset($this->_data[$k]);
                unset($this->_rows[$k]);
                break;
            }
        }
        $i = 0;
        foreach ($xml as $k => $element) {
            if ($element->{$this->getPrimaryKey()} == $id) {
                unset($xml->{$element->getName()}[$i]);
                return;
            }
            $i++;
        }


        if ($this->_filepath) {
            file_put_contents($this->_filepath, $this->_asPrettyXML($simpleXml->asXML()));
        }
        throw new Vps_Exception("Can't find entry with id '$id'");
    }

    private function _getSimpleXml()
    {
        if (!isset($this->_simpleXml)) {
	        if ($this->_xmlContent) {
	            $contents = $this->_xmlContent;
	        } else {
		        if (file_exists($this->_filepath)){
		            $contents = file_get_contents($this->_filepath);
		        }
	        }
	        $this->_simpleXml = new SimpleXMLElement($contents);
        }
        return $this->_simpleXml;
    }

    private function _getNewId ()
    {
        $simpleXml = $this->_getSimpleXml();
        $highestId = 0;
        foreach ($this->_getElements($simpleXml) as $f) {
            if (((int)$f->{$this->getPrimaryKey()}) > $highestId) $highestId = (int) $f->{$this->getPrimaryKey()};
        }
        return ++$highestId;
    }

    private function _getElements()
    {
        $simpleXml = $this->_getSimpleXml();

        if (!$simpleXml->xpath($this->_xpath)) {
            throw new Vps_Exception("Wrong Xpath '$this->_xpath' for model '".get_class($this)."'");
        }
        return $simpleXml->xpath($this->_xpath."/".$this->_topNode);
    }

    private function _getRootElement()
    {
        $simpleXml = $this->_getSimpleXml();
        $ret = $simpleXml->xpath($this->_xpath);
        if (!$ret) {
            throw new Vps_Exception("Wrong Xpath '$this->_xpath' for model '".get_class($this)."'");
        }
        return $ret[0];
    }

    public function getXmlContentString()
    {
        return $this->_getSimpleXml()->asXML();
    }

    public function getFilePath ()
    {
        return $this->_filepath;
    }

    protected function _asPrettyXML($string)
    {
        $indent = 3;
        /**
         * put each element on it's own line
         */
        $string =preg_replace("/>\s*</",">\n<",$string);

        /**
         * each element to own array
         */
        $xmlArray = explode("\n",$string);

        /**
         * holds indentation
         */
        $currIndent = 0;

        /**
         * set xml element first by shifting of initial element
         */
        $string = array_shift($xmlArray) . "\n";

        foreach($xmlArray as $element) {
            /** find open only tags... add name to stack, and print to string
             * increment currIndent
             */

            if (preg_match('/^<([\w])+[^>\/]*>$/U',$element)) {
                $string .=  str_repeat(' ', 0) . $element . "\n";
                $currIndent += $indent;
            }

            /**
             * find standalone closures, decrement currindent, print to string
             */
            elseif ( preg_match('/^<\/.+>$/',$element)) {
                $currIndent -= $indent;
                $string .=  str_repeat(' ', 0) . $element . "\n";
            }
            /**
             * find open/closed tags on the same line print to string
             */
            else {
                $string .=  str_repeat(' ', 0) . $element . "\n";
            }
        }

        return $string;

    }

}