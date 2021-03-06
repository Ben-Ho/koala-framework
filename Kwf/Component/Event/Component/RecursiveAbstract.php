<?php
/**
 * @package Component
 * @subpackage Event
 */
class Kwf_Component_Event_Component_RecursiveAbstract extends Kwf_Component_Event_Abstract
{
    /**
     * @var Kwf_Component_Data
     */
    public $component;

    public function __construct($componentClass, Kwf_Component_Data $component)
    {
        $this->class = $componentClass;
        $this->component = $component;
    }
}
