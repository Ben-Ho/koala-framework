<?php
class Vps_Controller_Action_User_UsersController extends Vps_Controller_Action_Auto_Grid
{
    protected $_buttons = array('add', 'userlock', 'userdelete'); // original delete button entfernt
    protected $_permissions = array('userlock' => true, 'userdelete' => true);
    protected $_sortable = true;
    protected $_defaultOrder = 'id';
    protected $_paging = 20;
    protected $_queryFields = array('id', 'email', 'firstname', 'lastname');
    protected $_editDialog = array('controllerUrl'=>'/vps/user/user',
                                   'width'=>550,
                                   'height'=>520);

    public function preDispatch()
    {
        $this->_model = Zend_Registry::get('userModel');
        parent::preDispatch();
    }

    protected function _initColumns()
    {
        parent::_initColumns();
        $this->_filters['text'] = array(
            'type'=>'TextField',
            'width' => 85
        );
        $this->_filters['lockedtoo'] = array(
            'type'      => 'Button',
            'skipWhere' => true,
            'icon'      => '/assets/silkicons/user_red.png',
            'cls'       => 'x-btn-text-icon',
            'text'      => trlVps('Show locked users'),
            'tooltip'   => trlVps('Show locked users too')
        );

        // alle erlaubten haupt-rollen in variable
        $roles = array();
        $acl = Vps_Registry::get('acl');
        $userRole = Vps_Registry::get('userModel')->getAuthedUserRole();
        foreach ($acl->getAllowedEditRolesByRole($userRole) as $role) {
            $roles[] = array($role->getRoleId(), $role->getRoleName());
        }
        $this->_filters['role'] = array(
            'type'=>'ComboBox',
            'width'=>120,
            'label' => trlVps('Rights').':',
            'defaultText' => trlVps('all'),
            'skipWhere' => true,
            'data' => $roles
        );

        $this->_columns->add(new Vps_Grid_Column_Button('edit', trlVps('Edit')));
        $this->_columns->add(new Vps_Grid_Column('id', 'ID', 50));
        $this->_columns->add(new Vps_Grid_Column('email', trlVps('Email'), 140));
        $this->_columns->add(new Vps_Grid_Column('role', trlVps('Rights')))
            ->setData(new Vps_Controller_Action_User_Users_RoleData());

        $this->_columns->add(new Vps_Grid_Column('gender', trlVps('Gender'), 70))
            ->setRenderer('genderIcon');
        $this->_columns->add(new Vps_Grid_Column('title', trlVps('Title'), 80));

        $this->_columns->add(new Vps_Grid_Column('firstname', trlVps('First name'), 110));
        $this->_columns->add(new Vps_Grid_Column('lastname', trlVps('Last name'), 110));

        if (isset($this->_getAuthData()->language)) {
             $this->_columns->add(new Vps_Grid_Column('language', trlVps('lang'), 30));
        }

        $this->_columns->add(new Vps_Grid_Column('password', trlVps('Activated'), 60))
            ->setRenderer('boolean');
        $this->_columns->add(new Vps_Grid_Column_Checkbox('locked', trlVps('Locked'), 60));

        $authedRole = Zend_Registry::get('userModel')->getAuthedUserRole();
        $acl = Zend_Registry::get('acl');
        if ($acl->getRole($authedRole) instanceof Vps_Acl_Role_Admin) {
            $this->_columns->add(new Vps_Grid_Column_Checkbox('webcode', trlVps('Only for this web'), 110))
                 ->setData(new Vps_Controller_Action_User_Users_WebcodeData());
        }
    }

    public function jsonUserDeleteAction()
    {
        if (!isset($this->_permissions['userdelete']) || !$this->_permissions['userdelete']) {
            throw new Vps_Exception("userdelete is not allowed.");
        }
        $ids = $this->getRequest()->getParam($this->_primaryKey);
        $ids = explode(';', $ids);

        $ownUserRow = Vps_Registry::get('userModel')->getAuthedUser();
        if (in_array($ownUserRow->id, $ids)) {
            throw new Vps_ClientException(trlVps("You cannot delete your own account."));
        }

        foreach ($ids as $id) {
            $row = $this->_model->getRow($id);
            if (!$row) {
                throw new Vps_ClientException("Can't find row with id '$id'.");
            }
            if (!$this->_hasPermissions($row, 'userdelete')) {
                throw new Vps_Exception("You don't have the permissions to delete this user.");
            }
            $row->deleted = 1;
            $row->save();
        }
    }

    public function jsonUserLockAction()
    {
        if (!isset($this->_permissions['userlock']) || !$this->_permissions['userlock']) {
            throw new Vps_Exception("userlock is not allowed.");
        }
        $ids = $this->getRequest()->getParam($this->_primaryKey);
        $ids = explode(';', $ids);

        $ownUserRow = Vps_Registry::get('userModel')->getAuthedUser();
        if (in_array($ownUserRow->id, $ids)) {
            throw new Vps_ClientException(trlVps("You cannot lock your own account."));
        }

        foreach ($ids as $id) {
            $row = $this->_model->getRow($id);
            if (!$row) {
                throw new Vps_ClientException("Can't find row with id '$id'.");
            }
            if (!$this->_hasPermissions($row, 'userlock')) {
                throw new Vps_Exception("You don't have the permissions to lock this user.");
            }
            $row->locked = $row->locked ? 0 : 1;
            $row->save();
        }
    }

    protected function _getSelect()
    {
        $select = parent::_getSelect();
        $acl = Zend_Registry::get('acl');
        $roles = array();
        foreach ($acl->getAllResources() as $res) {
            if ($res instanceof Vps_Acl_Resource_EditRole
                && $acl->isAllowed($this->_getUserRole(), $res, 'view')
            ) {
                $roles[] = $res->getRoleId();
            }
        }

        $select->whereEquals('deleted', 0);
        if (!$this->_getParam('query_lockedtoo')) {
            $select->whereEquals('locked', 0);
        }

        if ($roles) {
            $select->whereEquals('role', $roles);
        } else {
            $select = null;
        }

        if ($this->_getParam('query_role')) {
            if (in_array($this->_getParam('query_role'), $roles)) {
                $select->whereEquals('role', $this->_getParam('query_role'));
            } else {
                return null;
            }
        }

        return $select;
    }

    public function indexAction()
    {
        $config = array(
            'controllerUrl' => $this->getRequest()->getPathInfo()
        );
        $this->view->ext('Vps.User.Grid.Index', $config);
    }
}
