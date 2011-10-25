<?php
class Kwc_Basic_ImageEnlarge_EnlargeTag_Trl_Image_Component
    extends Kwc_Abstract_Image_Trl_Image_Component
{
    public function getImageData()
    {
        $d = $this->getData();
        while (!is_instance_of($d->componentClass, 'Kwc_Basic_ImageEnlarge_Trl_Component')) {
            $d = $d->parent;
        }

        if ($d->getComponent()->getRow()->own_image) {
            return $d->getChildComponent('-image')->getComponent()->getOwnImageData();
        }

        return $this->getData()->parent->chained->getComponent()->getImageData();
    }

    public function getOwnImageData()
    {
        return parent::getImageData();
    }

    public static function getMediaOutput($id, $type, $className)
    {
        //own_image kann sich ändern, daher zus. meta row fuer callback erstellen
        $d = Kwf_Component_Data_Root::getInstance()->getComponentById($id, array('ignoreVisible' => true));
        if (!$d) return null;

        while (!is_instance_of($d->componentClass, 'Kwc_Basic_ImageEnlarge_Trl_Component')) {
            $d = $d->parent;
        }
        $row = $d->getComponent()->getRow();
        /*
        Kwf_Component_Cache::getInstance()->saveMeta(
            $d,
            new Kwf_Component_Cache_Meta_Static_Callback($row->getModel())
        );
        */

        return parent::getMediaOutput($id, $type, $className);
    }
}
