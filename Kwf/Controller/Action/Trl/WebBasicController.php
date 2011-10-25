<?php
class Kwf_Controller_Action_Trl_WebBasicController extends Kwf_Controller_Action_Auto_Grid
{
    protected $_modelName = "Kwf_Trl_Model_Web";
    protected $_buttons = array('save');
    protected $_sortable = false;
    protected $_defaultOrder = 'id';
    protected $_paging = 30;
    protected $_columns;
    protected $_colNames = array();
    protected $_showAllLanguages = false;

    protected function _initColumns()
    {
        $this->_filters['text'] = array(
            'type'=>'TextField',
            'width'=>80
        );

        $config = Zend_Registry::get('config');
        $weblang = $config->webCodeLanguage;

        $this->_columns->add(new Kwf_Grid_Column('id'));
        $this->_columns->add(new Kwf_Grid_Column('context', trlKwf('Context')))
            ->setWidth(50);

        $languages = array();
        $languages[] = $weblang;
        $plural = array();
        $role = $this->_getAuthData()->role;
        $user_lang = $this->_getAuthData()->language;
        $showAllLanguages = $role == 'admin' || $this->_showAllLanguages;

        //defintion der zu übersetzenden sprachen
        if ($showAllLanguages) {
            if ($config->languages) {
                foreach($config->languages as $language) {
                    if ($language != $weblang) {
                        $languages[] = $language;
                    }
                }
            }
        } else {
            if ($user_lang != $weblang)
                $languages[] = $user_lang;
        }

        //ausgabe der einzahl Felder
        foreach ($languages as $lang) {
            //Singular
            if ($lang == $weblang) {
                $this->_columns->add(new Kwf_Grid_Column($lang, $lang." ".trlKwf("Singular")))
                    ->setWidth(200)
                    ->setRenderer('notEditable');
                $this->_colNames[] = $lang;
            } else {
                if ($lang == $user_lang || $showAllLanguages) {
                    $this->_columns->add(new Kwf_Grid_Column($lang, $lang." ".trlKwf("Singular")))
                        ->setEditor(new Kwf_Form_Field_TextField())
                        ->setWidth(200);;
                    $this->_colNames[] = $lang;
                }
            }
        }

        //Ausgabe der Plural felder
        foreach ($languages as $lang) {
            //Plural
            if ($lang == $weblang) {
                $this->_columns->add(new Kwf_Grid_Column($lang."_plural", $lang." ".trlKwf("Plural")))
                    ->setWidth(200)
                    ->setRenderer('notEditable');
                $this->_colNames[] = $lang."_plural";
            } else {
                if ($lang == $user_lang || $showAllLanguages) {
                    $this->_columns->add(new Kwf_Grid_Column($lang."_plural", $lang." ".trlKwf("Plural")))
                        ->setEditor(new Kwf_Form_Field_TextField())
                        ->setWidth(200);
                    $this->_colNames[] = $lang."_plural";
                }
            }
       }

        parent::_initColumns();
    }

    protected function _beforeSave(Kwf_Model_Row_Interface $row, $submitRow)
    {
        parent::_beforeSave($row, $submitRow);
        foreach ($this->_colNames as $colName)
        if (!$row->{$colName}) {
            $row->{$colName} = null;
        } else if ($row->$colName == ' ') {
            $row->$colName = '';
        }
    }
}