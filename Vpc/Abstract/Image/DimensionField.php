<?php
class Vpc_Abstract_Image_DimensionField extends Vps_Form_Field_Abstract
{
    public function __construct($name = null, $fieldLabel = null)
    {
        parent::__construct($name, $fieldLabel);
        $this->setXtype('vpc.image.dimensionfield');
    }

    public function load($row)
    {
        $value = array(
            'dimension' => $row->dimension,
            'width' => $row->width,
            'height' => $row->height,
            'scale' => $row->scale,
        );
        return array($this->getFieldName() => $value);
    }

    public function prepareSave(Vps_Model_Row_Interface $row, $postData)
    {
        Vps_Form_Field_Abstract::prepareSave($row, $postData);
        $value = $postData[$this->getFieldName()];
        if (is_string($value)) {
            $value = Zend_Json::decode($value);
        }
        if (!is_array($value)) $value = array();
        $row->dimension = isset($value['dimension']) ? $value['dimension'] : null;
        $row->width = isset($value['width']) ? $value['width'] : null;
        $row->height = isset($value['height']) ? $value['height'] : null;
        $row->scale = isset($value['scale']) ? $value['scale'] : null;
    }

    protected function _getValueFromPostData($postData)
    {
        $fieldName = $this->getFieldName();
        if (!isset($postData[$fieldName])) $postData[$fieldName] = null;
        return $postData[$fieldName];
    }

    public function validate($row, $postData)
    {
        $ret = parent::validate($row, $postData);

        if ($this->getInternalSave() !== false) {

            $data = $this->_getValueFromPostData($postData);
            if (!is_string($data)) {
                return $ret;
            }
            $data = Zend_Json::decode($data);
            $dimensions = $this->getDimensions();
            reset($dimensions);

            if ($data['dimension']) {
                $dimension = $dimensions[$data['dimension']];
            } else {
                $dimension = current($dimensions);
            }
            if ($dimension) {
                if (($dimension['scale'] == Vps_Media_Image::SCALE_BESTFIT ||
                    $dimension['scale'] == Vps_Media_Image::SCALE_CROP) &&
                    empty($data['width']) && empty($data['height'])
                ) {
                    $ret[] = trlVps('Dimension: At least width or height must be set higher than 0 when using crop or bestfit.');
                }
            }
        }
        return $ret;
    }
}
