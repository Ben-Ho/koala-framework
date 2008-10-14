<?php
class Vpc_Basic_Text_BlockStyleForm extends Vpc_Basic_Text_InlineStyleForm
{
    protected function _init()
    {
        parent::_init();
        $tag = $this->fields->insertAfter('name', new Vps_Form_Field_Select('tag', trlVps('Tag')))
            ->setValues(array(
                'p'    => trlVps('Normal (p)'),
                'h1'   => trlVps('Überschrift 1 (h1)'),
                'h2'   => trlVps('Überschrift 2 (h2)'),
                'h3'   => trlVps('Überschrift 3 (h3)'),
                'h4'   => trlVps('Überschrift 4 (h4)'),
                'h5'   => trlVps('Überschrift 5 (h5)'),
                'h6'   => trlVps('Überschrift 6 (h6)')
             ))
            ->setAllowBlank(false)
            ->setDefaultValue('p');

        $this->add(new Vps_Form_Field_NumberField('margin_top', trlVps('Margin top')))
            ->setAllowNegative(false)
            ->setAllowDecimals(false)
            ->setWidth(50);
        $this->add(new Vps_Form_Field_NumberField('margin_bottom', trlVps('Margin bottom')))
            ->setAllowNegative(false)
            ->setAllowDecimals(false)
            ->setWidth(50);
        $this->add(new Vps_Form_Field_Select('text_align', trlVps('Text Align')))
            ->setShowNoSelection(true)
            ->setValues(array(
                'left' => trlVps('Left'),
                'right' => trlVps('Right'),
                'center' => trlVps('Center'),
                'justify' => trlVps('Justify')
            ));
    }
}
