<?php
abstract class Kwf_Controller_Action extends Zend_Controller_Action
{
    public function jsonIndexAction()
    {
        $this->indexAction();
    }

    public function preDispatch()
    {
        Kwf_Util_Https::ensureHttps();

        if (!$this instanceof Kwf_Controller_Action_Error_ErrorController
                && $this->_getParam('application_max_assets_mtime')
                && $this->getHelper('ViewRenderer')->isJson()) {
            $l = new Kwf_Assets_Loader();
            if ($l->getDependencies()->getMaxFileMTime()!= $this->_getParam('application_max_assets_mtime')) {
                $this->_forward('json-wrong-version', 'error',
                                    'kwf_controller_action_error');
                return;
            }
        }

        $allowed = false;
        $acl = $this->_getAcl();
        $resource = $this->getRequest()->getResourceName();
        if ($resource == 'kwf_user_changeuser') {
            //spezielle berechtigungsabfrage für Benutzerwechsel
            $role = Zend_Registry::get('userModel')->getAuthedChangedUserRole();
            $allowed = $acl->isAllowed($role, $resource, 'view');
        } else if ($this->_getUserRole() == 'cli') {
            $allowed = $acl->isAllowed('cli', $resource, 'view');
        } else if ($resource == 'kwf_component') {
            $allowed = $this->_isAllowedComponent(); // Bei Test ist niemand eingeloggt und deshalb keine Prüfung
        } else {
            if (!$acl->has($resource)) {
                throw new Kwf_Exception_NotFound();
            } else {
                if ($this->_getAuthData()) {
                    $allowed = $acl->isAllowedUser($this->_getAuthData(), $resource, 'view');
                } else {
                    $allowed = $acl->isAllowed($this->_getUserRole(), $resource, 'view');
                }
            }
        }
        if ($allowed) {
            if ($this->_getUserRole() == 'cli') {
                $allowed = $this->_isAllowed('cli');
            } else {
                $allowed = $this->_isAllowed($this->_getAuthData());
            }
        }

        if (!$allowed) {
            $params = array(
                'resource' => $resource,
                'role' => $this->_getUserRole()
            );
            if ($this->getHelper('ViewRenderer')->isJson()) {
                $this->_forward('json-login', 'login',
                                    'kwf_controller_action_user', $params);
            } else {
                $params = array('location' => $this->getRequest()->getPathInfo());
                $this->_forward('index', 'login',
                                    'kwf_controller_action_user', $params);
            }
        }
    }

    protected function _isAllowed($user)
    {
        return true;
    }

    protected function _isAllowedComponent()
    {
        $actionName = $this->getRequest()->getActionName();
        if ($actionName != 'json-index' && $actionName != 'index') {
            $authData = $this->_getAuthData();
            $class = $this->_getParam('class');
            $componentId = $this->_getParam('componentId');
            if (!$componentId) {
                return Kwf_Registry::get('acl')->isAllowedComponent($class, $authData);
            } else {
                return Kwf_Registry::get('acl')->isAllowedComponentById($componentId, $class, $authData);
            }
        }
        return true;
    }

    public function postDispatch()
    {
        Kwf_Component_ModelObserver::getInstance()->process();
    }

    protected function _getUserRole()
    {
        if (php_sapi_name() == 'cli') return 'cli';
        return Kwf_Registry::get('userModel')->getAuthedUserRole();
    }

    protected function _getAuthData()
    {
        if (php_sapi_name() == 'cli') return null;
        return Kwf_Registry::get('userModel')->getAuthedUser();
    }
    /**
     * @return Kwf_Acl
     */
    protected function _getAcl()
    {
        return Zend_Registry::get('acl');
    }
}
