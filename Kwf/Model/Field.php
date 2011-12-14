<?php
/**
 * @package Model
 */
class Kwf_Model_Field extends Kwf_Model_Abstract implements Kwf_Model_SubModel_Interface
{
    protected $_rowClass = 'Kwf_Model_Field_Row';
    protected $_rowsetClass = 'Kwf_Model_Field_Rowset';
    protected $_fieldName;
    protected $_columns = array();

    public function __construct(array $config = array())
    {
        if (isset($config['fieldName'])) {
            $this->_fieldName = $config['fieldName'];
        }
        if (isset($config['columns'])) $this->_columns = (array)$config['columns'];
        parent::__construct($config);
    }

    public function getRow($select)
    {
        throw new Kwf_Exception('getRow');
    }

    public function getRows($where=null, $order=null, $limit=null, $start=null)
    {
        throw new Kwf_Exception('getRows is not possible for Kwf_Model_Field');
    }

    public function countRows($select = array())
    {
        throw new Kwf_Exception('countRows is not possible for Kwf_Model_Field');
    }

    public function isEqual(Kwf_Model_Interface $other)
    {
        throw new Kwf_Exception('isEqual is not possible for Kwf_Model_Field');
    }

    public function getPrimaryKey()
    {
        return null;
    }

    protected function _getOwnColumns()
    {
        return $this->_columns;
    }

    public function getRowBySiblingRow(Kwf_Model_Row_Interface $siblingRow)
    {
        $data = $siblingRow->{$this->_fieldName};
        if (is_string($data)) {
            if (substr($data, 0, 13) == 'kwfSerialized') {
                //früher wurde es mal so gespeichert
                $data = substr($data, 13);
            }
            if (substr($data, 0, 2) == 'a:') {
                //früher wurde es mal so gespeichert, das 35000 update script sollte es konvertieren
                //erwischt aber manchmal nicht alles
                try {
                    $data = unserialize($data);
                } catch (Exception $e) {
                    $e = new Kwf_Exception($e->getMessage(). " $data");
                    $e->logOrThrow();
                    $data = false;
                }
            } else {
                // json_decode gibt auch keinen fehler aus, wenn man ihm einen
                // falschen string (zB serialized) übergibt. bei nicht-json-daten
                // kommt immer null raus. Da bringt das try-catch eher wenig,
                // weil null nunmal keine Exception ist.
                // Lösung: Wir schmeissen die exception händisch im falle von
                // NULL. Eventuelles PROBLEM dabei ist jedoch,
                // wenn man: $data = json_decode(json_encode(NULL))
                // macht, weil dann korrekterweise NULL rauskommen würde.
                // deshalb wird dieser fall separat ohne dem json_decode behandelt
                if ($data == 'null' || $data == '') {
                    $data = null;
                } else {
                    $decodedData = json_decode($data);
                    if (is_null($decodedData)) { // json_encode hat nicht funktioniert, siehe mörder-kommentar paar zeilen vorher
                        $e = new Kwf_Exception("json_decode failed. Input data was: '$data'");
                        $e->logOrThrow();
                    }
                    $data = $decodedData;
                }
            }
        }
        if (!$data) {
            $data = $this->getDefault();
        }
        $data = (array)$data;

        return new $this->_rowClass(array(
            'model' => $this,
            'siblingRow' => $siblingRow,
            'data' => $data
        ));
    }

    public function getFieldName()
    {
        return $this->_fieldName;
    }

    public function getUniqueIdentifier() {
        throw new Kwf_Exception("no unique identifier set");
    }
}
