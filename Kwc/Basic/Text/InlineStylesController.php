<?php
class Kwc_Basic_Text_InlineStylesController extends Kwf_Controller_Action_Auto_Grid
{
    protected $_buttons = array('add', 'delete');
    protected $_position = 'pos';

    public function init()
    {
        $class = $this->_getParam('componentClass');
        if (!Kwc_Abstract::getSetting($class, 'enableStyles') ||
            !Kwc_Abstract::getSetting($class, 'enableStylesEditor')
        ) {
            throw new Kwf_Exception("Styles are disabled");
        }
        $this->_model = Kwf_Model_Abstract::getInstance(Kwc_Abstract::getSetting($class, 'stylesModel'));
        parent::init();
    }

    protected function _initColumns()
    {
        parent::_initColumns();
        $this->_columns->add(new Kwf_Grid_Column('name', 'Name', 100));
    }

    protected function _formatSelectTag($select)
    {
        $select->whereEquals('tag', 'span');
    }

    protected function _getSelect()
    {
        $ret = parent::_getSelect();

        $pattern = Kwc_Abstract::getSetting($this->_getParam('componentClass'),
                                                            'stylesIdPattern');
        if ($pattern) {
            if (preg_match('#'.$pattern.'#', $this->_getParam('componentId'), $m)) {
                $ret->whereEquals('ownStyles', $m[0]);
            }
        } else {
            $ret->whereEquals('ownStyles', '');
        }

        $this->_formatSelectTag($ret);
        return $ret;
    }

    protected function _beforeDelete(Kwf_Model_Row_Interface $row)
    {
        if ($this->_getUserRole() != 'admin' && $row->master) {
            throw new Kwf_ClientException(trlKwf("You can't delete master styles"));
        }
    }

}
