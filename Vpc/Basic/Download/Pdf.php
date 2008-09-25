<?php
class Vpc_Basic_Download_Pdf extends Vpc_Abstract_Pdf
{
    public function writeContent()
    {
        $fileSizeHelper = new Vps_View_Helper_FileSize();
        $encodeTextHelper = new Vps_View_Helper_MailEncodeText();
        $vars = $this->_component->getTemplateVars();
        if ($vars['iconname']) {
            $icon = new Vps_Asset($vars['iconname']);
            $this->_pdf->Image($icon->getFilename(), $this->_pdf->getX(), $this->_pdf->getY(), 3, 3, 'PNG');
        }
        $this->_pdf->setX($this->_pdf->getX() + 4);
        if ($vars['filesize']) {
            $filesize = ' (' . $fileSizeHelper->fileSize($vars['filesize']) . ')';
        } else {
            $filesize = '';
        }
        //Hier keine normale Textbox, da diese einen Link nicht unterstützt
        $this->_pdf->Cell(0, 0, $encodeTextHelper->mailEncodeText($vars['infotext'].$filesize), '', 1, '', 0);
        $this->Ln(1);

    }

}
