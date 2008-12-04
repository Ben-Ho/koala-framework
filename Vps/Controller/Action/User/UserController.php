<?php
class Vps_Controller_Action_User_UserController extends Vps_Controller_Action_Auto_Form
{
    private $_permissionFieldset;

    public function preDispatch()
    {
        $regUserForm = Vps_Registry::get('config')->user->form;
        if (is_string($regUserForm)) {
            $this->_formName = $regUserForm;
        } else {
            $this->_formName = $regUserForm->grid;
        }
        parent::preDispatch();
    }

    protected function _initFields()
    {
        parent::_initFields();

        $fs = $this->_form->prepend(new Vps_Form_Container_FieldSet(trlVps('Advice')));
        $fs->setLabelWidth(80);

        $fs->add(new Vps_Form_Field_Panel())
            ->setHtml(trlVps('At the following action emails are automatically sent to the adequade user.').'<br />'
                     .trlVps('Create, Delete and E-Mail change'));
        $fs->add(new Vps_Form_Field_ShowField('password', trlVps('Activation link')))
            ->setData(new Vps_Controller_Action_User_Users_ActivationlinkData());

        if ($roleField = $this->_getRoleField()) {
            $this->_getPermissionFieldset()->add($roleField);
        }

        $authedRole = Zend_Registry::get('userModel')->getAuthedUserRole();
        if (Vps_Registry::get('acl')->getRole($authedRole) instanceof Vps_Acl_Role_Admin) {
            $this->_getPermissionFieldset()->add(new Vps_Form_Field_Checkbox('webcode', trlVps('Webcode')))
                ->setData(new Vps_Controller_Action_User_Users_WebcodeData());
        }
    }

    private function _getPermissionFieldset()
    {
        if (!$this->_permissionFieldset) {
            $this->_permissionFieldset = $this->_form->add(new Vps_Form_Container_FieldSet(trlVps('Permissions')));
            $this->_permissionFieldset->setLabelWidth(100);
        }
        return $this->_permissionFieldset;
    }

    protected function _getRoleField()
    {
        $acl = Vps_Registry::get('acl');
        $userRole = Vps_Registry::get('userModel')->getAuthedUserRole();
        $authedUser = Vps_Registry::get('userModel')->getAuthedUser();

        // alle erlaubten haupt-rollen in variable
        $roles = array();
        foreach ($acl->getAllowedEditRolesByRole($userRole) as $role) {
            $roles[$role->getRoleId()] = $role->getRoleName();
        }
        if (!$roles) return null;

        // ALLE additional roles in variable
        $addRoles = array();
        foreach ($acl->getAdditionalRoles() as $role) {
            $addRoles[$role->getParentRoleId()][$role->getRoleId()] = $role->getRoleName();
        }


        // Wenns keine additionalRoles gibt, normales select verwenden
        if (!$addRoles) {
            $ret = new Vps_Form_Field_Select('role', trlVps('Rights'));
            $ret->setValues($roles)->setAllowBlank(false);
        } else {
            // eigene additionalRoles holen, nur die dürfen zugewiesen werden
            $allowedRoles = array_merge(
                $authedUser->getAdditionalRoles(),
                $acl->getAllowedEditResourceRoleIdsByRole($userRole)
            );

            // cards container erstellen und zu form hinzufügen
            $ret = new Vps_Form_Container_Cards('role', trlVps('Rights'));
            $ret->setAllowBlank(false);
            foreach ($roles as $roleId => $roleName) {
                $card = $ret->add();
                $card->setTitle($roleName);
                $card->setName($roleId);

                if (isset($addRoles[$roleId])) {
                    foreach ($addRoles[$roleId] as $addRoleId => $addRoleName) {
                        if (!in_array($addRoleId, $allowedRoles)) {
                            unset($addRoles[$roleId][$addRoleId]);
                        }
                    }

                    $editor = new Vps_Form_Field_MultiCheckbox('Vps_Model_User_AdditionalRoles', trlVps('Additional rights'));
                    $editor->setColumnName('additional_role');
                    $editor->setValues($addRoles[$roleId]);

                    $card->add($editor);
                }
            }
        }
        return $ret;
    }
}
