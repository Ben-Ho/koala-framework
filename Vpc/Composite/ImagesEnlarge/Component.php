<?php
class Vpc_Composite_ImagesEnlarge_Component extends Vpc_Composite_Images_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['child']['component'] = 'Vpc_Basic_ImageEnlarge_Component';
        $ret['componentName'] = trlVps('Gallery');
        $ret['assets']['dep'][] = 'VpsEnlargeNextPrevious';
        return $ret;
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        $images = $this->getData()->getChildComponents(array(
            'generator' => 'child'
        ));
        $ret['smallMaxWidth'] = 0;
        $ret['smallMaxHeight'] = 0;
        foreach ($images as $image) {
            $img = $image->getComponent()->getImageDimensions();
            $ret['smallMaxWidth'] = max($ret['smallMaxWidth'], $img['width']);
            $ret['smallMaxHeight'] = max($ret['smallMaxHeight'], $img['height']);
        }

        return $ret;
    }

    public function getCacheVars()
    {
        $ret = parent::getCacheVars();
        $ret[] = $this->_getCacheVars();
        $images = $this->getData()->getChildComponents(array(
            'generator' => 'child'
        ));
        foreach ($images as $image) {
            $ret[] = array(
                'model' => $this->getComponent()->getOwnModel(),
                'id' => $image->dbId,
                'field' => 'component_id'
            );
        }
        return $ret;
    }
}
