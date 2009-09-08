<?php
class Vpc_Basic_ImageEnlarge_Component extends Vpc_Abstract_Image_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['componentName'] = trlVps('Image Enlarge');
        $ret['componentIcon'] = new Vps_Asset('imageEnlarge');
        $ret['generators']['child']['component']['linkTag'] = 'Vpc_Basic_ImageEnlarge_EnlargeTag_Component';
        return $ret;
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        return $ret;
    }

    public function getImageRow()
    {
        $c = $this->getData()->getChildComponent('-linkTag');
        if (is_instance_of($c->componentClass, 'Vpc_Basic_LinkTag_Component')) {
            $c = $c->getChildComponent('-link');
        }
        if (is_instance_of($c->componentClass, 'Vpc_Basic_ImageEnlarge_EnlargeTag_Component')) {
            if (Vpc_Abstract::getSetting($c->componentClass, 'alternativePreviewImage')
                && $c->getComponent()->getRow()->preview_image
            ) {
                $r = $c->getComponent()->getAlternativePreviewImageRow();
                if ($r->imageExists()) {
                    return $r;
                }
            }
        }
        return parent::getImageRow();
    }

    public function getOwnImageRow()
    {
        return parent::getImageRow();
    }
}
