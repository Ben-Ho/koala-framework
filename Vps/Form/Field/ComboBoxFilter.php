<?php
/**
 * Für zwei (oder mehr) zusammengeschaltete ComboBoxen (Auswahl in der ersten
 * lädt Daten in der zweiten nach, gespeichert wird nur die zweite wenn nicht anders angegeben)
 **/
class Vps_Form_Field_ComboBoxFilter extends Vps_Form_Field_Select
{
    public function __construct($field_name = null, $field_label = null)
    {
        parent::__construct($field_name, $field_label);
        $this->setXtype('comboboxfilter');
        $this->setSave(false);
    }

    //setFilteredCombo(combo)


    public function getMetaData()
    {
        $ret = parent::getMetaData();

        $saveCombo = $this->getFilteredCombo();
        $saveMetaData = $saveCombo->getMetaData();

        $filterMetaData = $ret;
        $filterMetaData['xtype'] = 'combobox';

        if (!$saveCombo->getFilterField()) {
            throw new Vps_Exception("setFilterField(str) must be called for the save-combo-box");
        }

        $data = $saveCombo->getValues();
        if (is_array($data)) {
            $saveMetaData['store']['data'] = array();
            foreach ($data as $k=>$i) {
                $addArray = array();
                foreach ($i as $i2) {
                    $addArray[] = $i2;
                }
                $saveMetaData['store']['data'][] = $addArray;
            }
        }

        $ret['items'] = array(
            $filterMetaData,
            $saveMetaData
        );

        return $ret;
    }

    public function prepareSave(Vps_Model_Row_Interface $row, $postData)
    {
        parent::prepareSave($row, $postData);
        $filteredCombo = $this->getFilteredCombo();
        if ($filteredCombo->getSave() !== false && $filteredCombo->getInternalSave() !== false) {
            $filteredCombo->prepareSave($row, $postData);
        }
    }

    public function processInput($row, $postData)
    {
        $postData = parent::processInput($row, $postData);
        if (!$this->getFilteredCombo()) {
            throw new Vps_Exception("No filteredCombo set");
        }
        $value = $this->_getValueFromPostData($postData);
        if ($value) {
            $filtered = $this->getFilteredCombo();
            $filtered->setFilterValue($value);
        }
        return $postData;
    }

    public function getTemplateVars($values, $fieldNamePostfix = '')
    {
        $ret = parent::getTemplateVars($values, $fieldNamePostfix);

        //TODO: Wenn wir mal Ext-Forms im Frontend haben das hier entfernen
        $onclick = "onchange=\"this.form.submit();\"";
        $ret['html'] = str_replace("<select ", "<select $onclick ", $ret['html']);

        $ret['html'] .= '<input type="submit" value="»" />';
        return $ret;
    }
}
