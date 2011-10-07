<?php
class Vps_Component_Generator_InheritDifferentComponentClass_Box_Component extends Vpc_Abstract
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['flags']['hasAlternativeComponent'] = true;
        return $ret;
    }

    public static function getAlternativeComponents()
    {
        return array(
            'inherit'=>'Vps_Component_Generator_InheritDifferentComponentClass_Box_Inherit_Component'
        );
    }

    public static function useAlternativeComponent($componentClass, $parentData, $generator)
    {
        $c = $parentData;
        while (!$c->inherits) $c = $c->parent;

        $c = $c->parent;
        if (!$c) return false;
        while (!$c->inherits) $c = $c->parent;

        $instances = Vps_Component_Generator_Abstract::getInstances($c, array(
                'inherit' => true
        ));
        if (in_array($generator, $instances, true)) {
            //wir wurden geerbt weils über uns ein parentData mit dem gleichen generator gibt
            return 'inherit';
        } else {
            return false;
        }
    }
}
