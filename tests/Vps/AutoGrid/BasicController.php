<?php
class Vps_AutoGrid_BasicController extends Vps_Controller_Action_Auto_Grid
{
    protected $_defaultOrder = 'id';

    public function indexAction()
    {
        $this->view->assetsType = 'Vps_AutoGrid:Test';
        $this->view->viewport = 'Vps.Test.Viewport';
        parent::indexAction();
    }

    public function preDispatch()
    {
        $this->_filters = array(
            'text' => true,
            'type' => array(
                'type' => 'ComboBox',
                'data' => array(array('A', 'A'), array('B', 'B'), array('C', 'C'))
            ),
            'value' => array(
                'type'      => 'Button',
                'skipWhere' => true,
                'text' => 'Filter'
            )
        );

        $this->_model = new Vps_Model_FnF();
        $this->_model->setData(array(
            array('id' => 1, 'value' => 'Herbert', 'testtime' => '2008-12-03', 'type' => 'A'),
            array('id' => 2, 'value' => 'Kurt', 'testtime' => '2008-12-06', 'type' => 'A'),
            array('id' => 3, 'value' => 'Klaus', 'testtime' => '2008-12-09', 'type' => 'B'),
            array('id' => 4, 'value' => 'Rainer', 'testtime' => '2008-12-12', 'type' => 'B'),
            array('id' => 5, 'value' => 'Franz', 'testtime' => '2008-12-10', 'type' => 'C'),
            array('id' => 6, 'value' => 'Niko', 'testtime' => '2008-12-15', 'type' => 'C'),
            array('id' => 7, 'value' => 'Lorenz', 'testtime' => '2008-12-18', 'type' => 'C'),
        ));
        $this->_model->setColumns(array('id', 'value', 'testtime', 'type'));
        parent::preDispatch();
    }

    protected function _initColumns()
    {

        $this->_columns->add(new Vps_Grid_Column('id', 'Id', 50));
        $this->_columns->add(new Vps_Grid_Column('value', 'Context', 100));
        $this->_columns->add(new Vps_Grid_Column('type', 'Type', 50));
        parent::_initColumns();


    }

    public function fetchData($order, $limit, $start)
    {
        return $this->_fetchData($order, $limit, $start);
    }
}