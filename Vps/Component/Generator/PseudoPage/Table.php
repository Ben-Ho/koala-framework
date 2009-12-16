<?php
class Vps_Component_Generator_PseudoPage_Table extends Vps_Component_Generator_Table
    implements Vps_Component_Generator_PseudoPage_Interface
{
    protected $_filenameColumn;
    protected $_uniqueFilename;
    protected $_nameColumn;
    protected $_maxFilenameLength;

    protected function _init()
    {
        parent::_init();
        if (isset($this->_uniqueFilename)) {
            $this->_settings['uniqueFilename'] = $this->_uniqueFilename;
        }
        if (!isset($this->_settings['uniqueFilename'])) $this->_settings['uniqueFilename'] = false;

        if (isset($this->_filenameColumn)) {
            $this->_settings['filenameColumn'] = $this->_filenameColumn;
        }
        if (!isset($this->_settings['filenameColumn'])) $this->_settings['filenameColumn'] = false;

        if (isset($this->_nameColumn)) {
            $this->_settings['nameColumn'] = $this->_nameColumn;
        }
        if (!isset($this->_settings['nameColumn'])) $this->_settings['nameColumn'] = false;

        if (isset($this->_maxFilenameLength)) {
            $this->_settings['maxFilenameLength'] = $this->_maxFilenameLength;
        }
        if (!isset($this->_settings['maxFilenameLength'])) $this->_settings['maxFilenameLength'] = 100;
    }

    protected function _formatSelectFilename(Vps_Component_Select $select)
    {
        if ($select->hasPart(Vps_Component_Select::WHERE_FILENAME)) {
            $filename = $select->getPart(Vps_Component_Select::WHERE_FILENAME);
            if ($this->_settings['uniqueFilename']) {
                $select->whereEquals($this->_settings['filenameColumn'], $filename);
            } else {
                if ($this->_hasNumericIds) {
                    $pattern = '#^([0-9]+)_#';
                } else {
                    $pattern = '#^([^_]+)_#';
                }
                if (!preg_match($pattern, $filename, $m)) return null;
                $select->whereId($this->_idSeparator . $m[1]);
            }
        }
        return $select;
    }

    protected function _getNameFromRow($row)
    {
        if ($this->_settings['nameColumn']) {
            return $row->{$this->_settings['nameColumn']};
        } else {
            return $row->__toString();
        }
    }

    protected function _getFilenameFromRow($row)
    {
        if ($this->_settings['filenameColumn']) {
            if (!isset($row->{$this->_settings['filenameColumn']})) {
                throw new Vps_Exception("filenameColumn '".$this->_settings['filenameColumn']."' does not exist in row (Generator: ".get_class($this).")");
            }
            return $row->{$this->_settings['filenameColumn']};
        } else {
            return $this->_getNameFromRow($row);
        }
    }
    protected function _formatConfig($parentData, $row)
    {
        $data = parent::_formatConfig($parentData, $row);

        $data['filename'] = '';
        if (!$this->_settings['uniqueFilename']) {
            $data['filename'] .= $this->_getIdFromRow($row).'_';
        }
        $data['filename'] .= Vps_Filter::filterStatic($this->_getFilenameFromRow($row), 'Ascii');
        if (strlen($data['filename']) > $this->_settings['maxFilenameLength']) {
            $data['filename'] = substr($data['filename'], 0, $this->_settings['maxFilenameLength']);
        }

        $data['rel'] = '';
        $data['isPseudoPage'] = true;
        return $data;
    }
}
