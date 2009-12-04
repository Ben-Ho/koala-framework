<?php
class Vpc_Basic_Table_SettingsForm extends Vpc_Abstract_Form
{
    protected function _initFields()
    {
        parent::_initFields();

        $this->setCreateMissingRow(true);

        $this->add(new Vps_Form_Field_NumberField('columns', trlVps('How many columns')))
            ->setMinValue(1)
            ->setAllowBlank(false)
            ->setWidth(50);

        $tableStyles = Vpc_Abstract::getSetting($this->getClass(), 'tableStyles');
        if (count($tableStyles)) {
            $this->add(new Vps_Form_Field_Select('table_style', trlVps('Table style')))
                ->setShowNoSelection(true)
                ->setValues($tableStyles);
        }
    }
}
