<?php
abstract class Kwc_News_Detail_Abstract_Component extends Kwc_Directories_Item_Detail_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['child']['component']['content'] = 'Kwc_Paragraphs_Component';
        $ret['cssClass'] = 'webStandard';
        $ret['placeholder']['backLink'] = trlKwf('Back to overview');
        $ret['editComponents'] = array('content');
        $ret['flags']['hasFulltext'] = true;
        return $ret;
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        $ret['title'] = $this->getData()->row->title;
        $ret['publish_date'] = $this->getData()->row->publish_date;
        return $ret;
    }

    public function hasContent()
    {
        return $this->getData()->getChildComponent('-content')->hasContent();
    }

    public static function modifyItemData(Kwf_Component_Data $new)
    {
        parent::modifyItemData($new);
        $new->publish_date = $new->row->publish_date;
        $new->teaser = $new->row->teaser;
    }

    public function modifyFulltextDocument(Zend_Search_Lucene_Document $doc)
    {
        $field = Zend_Search_Lucene_Field::Keyword('kwcNews', 'kwcNews', 'utf-8');
        $field->boost = 0.0001;
        $doc->addField($field);

        return $doc;
    }
}
