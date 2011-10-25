<?php
class Kwf_Controller_Action_Trl_KwfController extends Kwf_Controller_Action_Auto_Grid
{
    protected $_modelName = "Kwf_Trl_Model_Kwf";
    protected $_buttons = array();
    protected $_sortable = true;
    protected $_defaultOrder = 'id';
    protected $_paging = 30;
    protected $_editDialog = array('controllerUrl'=>'/kwf/trl/kwf-edit',
                                   'width'=>600,
                                   'height'=>550);

    protected function _initColumns()
    {
        $lang = $this->_getLanguage();

        $this->_filters['text'] = array(
            'type'=>'TextField',
            'width'=>80,
            'queryFields' => array($lang, $lang.'_plural')
        );


        $this->_columns->add(new Kwf_Grid_Column_Button('edit'));
        $this->_columns->add(new Kwf_Grid_Column('id', 'Id', 50));
        $this->_columns->add(new Kwf_Grid_Column('context', trlKwf('Context'), 100));
        $this->_columns->add(new Kwf_Grid_Column($lang, $lang.' '.trlKwf('Singular'), 350));
        $this->_columns->add(new Kwf_Grid_Column($lang.'_plural', $lang.' '.trlKwf('Plural'), 150));

        $langs = self::getLanguages();
        if ($langs) {
            foreach ($langs as $lang) {
                if ($lang != $this->_getLanguage()) {
                    $this->_columns->add(new Kwf_Grid_Column($lang, $lang.' '.trlKwf('Singular'), 350));
                    $this->_columns->add(new Kwf_Grid_Column($lang.'_plural', $lang.' '.trlKwf('Plural'), 150));
                }
            }
        }

        parent::_initColumns();
    }

    protected function _getLanguage()
    {
        return 'en';
    }

    static public function getLanguages()
    {
        $config = Zend_Registry::get('config');
        $langs = array();
        if ($config->webCodeLanguage) $langs[] = $config->webCodeLanguage;
        if ($config->languages) {
            foreach ($config->languages as $lang) {
                $langs[] = $lang;
            }
            $langs = array_values(array_unique($langs));
        }
        if (Kwf_Component_Data_Root::getComponentClass()) {
            //TODO besser wär getComponentByFlag('hasLanguage') aber das gibt snicht
            $lngs = Kwf_Component_Data_Root::getInstance()
                ->getComponentsByClass('Kwc_Root_TrlRoot_Chained_Component', array('ignoreVisible'=>true)); 
            foreach ($lngs as $c) {
                $langs[] = $c->getComponent()->getLanguage();
            }
        }
        return array_unique($langs);
    }

    public function indexAction ()
    {
        $config = array(
            'controllerUrl' => $this->getRequest()->getPathInfo(),
            'language' => 'en'
        );
        $this->view->ext('Kwf.Trl.Grid', $config);
    }
}