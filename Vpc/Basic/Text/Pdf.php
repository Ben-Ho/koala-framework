<?php
class Vpc_Basic_Text_Pdf extends Vpc_Abstract_Pdf
{
    private $_wholeHTMLText = "";
    public function writeContent()
    {
        $vars = $this->_component->getTemplateVars();
        $contentParts = $vars['contentParts'];
        $html = '';
        foreach ($contentParts as $content){
            if (is_string($content)){

                $html .= str_replace("", "", $content);
                $html = preg_replace('# +#', ' ', $html);
                $html = str_replace('&nbsp;', ' ', $html);
                $html = preg_replace('#<br[^>]*> *</li#', '</li', $html);

                $html = preg_replace('#>\s+<#', '><', $html);
                $html = preg_replace('#<br *[/]>\s+#', '<br />', $html);
                $html = preg_replace('#</p>\s+#', '<p />', $html);
                $html = preg_replace('#<p>\s+#', '<p>', $html);

                $html = preg_replace('#<br *[/]> *</p> *<[A-Za-z]l#', '<br><ul', $html);
                $html = preg_replace('#</p> *<[A-Za-z]l#', '<br><ul', $html);
                $html = preg_replace('#</ul> *<p>#', '</ul>', $html);

                $html = preg_replace('#<br *[/]> *</p> *<p>#', '<br><br>', $html);
                $html = preg_replace('#</p> *<p>#', '<br><br>', $html);
                $html = preg_replace('#<p> *<br /> *</p>#', '<br>', $html);
                $html = preg_replace('#</p>#', '', $html);

                $html = preg_replace('#<p[^>]*>#', '<br>', $html);
                $html = preg_replace('#<p[^>]*///>#', '<br/>', $html);
                $html = str_replace("\n", "", $html);

            } elseif ($content['type'] == 'image') {
                $this->textAreaHTML($html);
                $html = '';
                $content['component']->getComponent()->getPdfWriter($this->_pdf)->writeContent();

            } elseif ($content['type'] == 'link') {
                $html .= "<a href=\"".$content['component']->url."\">";
            } else {
                //TODO andere Komponenten aufrufen
            }
        }

        $this->textAreaHTML($html);
    }
}
