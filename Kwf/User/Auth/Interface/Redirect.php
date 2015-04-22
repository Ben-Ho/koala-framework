<?php
/**
 * Auth Method Interface that can be used to authenticate users using third party sso services
 */
interface Kwf_User_Auth_Interface_Redirect
{
    /**
     * @return bool if this auth method should be shown for frontend (Component based) logins
     */
    public function showInFrontend();

    /**
     * @return bool if this auth method should be shown for admin (Ext based) logins
     */
    public function showInBackend();

    /**
     * Returns label used for this auth method.
     *
     * Following indizes can be used
     * - name: Name of this Auth Method
     * - linkText: Link Text when showing the user a text link to login
     * - icon: icon shown when showing the user a link to login
     *
     * @return array
     */
    public function getLoginRedirectLabel();

    /**
     * Returns array fields that must be shown to the user when using this auth method.
     *
     * Example:
     *  array(
     *      array(
     *          'name' => 'portal',
     *          'type' => 'select',
     *          'label' => 'Portal',
     *          'values' => array('foo'=>'Foo', 'bar'=>'Bar')
     *      )
     *  )
     *
     * @return array
     */
    public function getLoginRedirectFormOptions();

    /**
     * Returns the Url the User should be redirected to when using this auth method
     *
     * @param string Url the user should be redirected back after login
     * @param string State that should be passed to login and passed back, will be validated against saved session value
     * @param array Values as posted by FormOptions, array() if no form options exist
     */
    public function getLoginRedirectUrl($redirectBackUrl, $state, $formValues);

    /**
     * Returns the (existing) user that should be logged in by $params. The User must be already associated
     * to this sso account.
     *
     * @param string Url the user should be redirected back after login. Can be caused for OAuth2 calls.
     * @param array Request params as posted to the $redirectBackUrl
     */
    public function getUserToLoginByParams($redirectBackUrl, array $params);

    /**
     * Associate an (existing) user to an sso account by $params.
     *
     * @param Kwf_Model_Row_Interface User Row
     * @param string Url the user should be redirected back after login. Can be caused for OAuth2 calls.
     * @param array Request params as posted to the $redirectBackUrl
     */
    public function associateUserByParams(Kwf_Model_Row_Interface $user, $redirectBackUrl, array $params);

    /**
     * Return example links that can be used for development.
     *
     * @return array usually an empty array
     */
    public function createSampleLoginLinks($absoluteUrl);

    /**
     * If password should be allowed for this user.
     *
     * Can return false if a user exists in sso service and we want to prevent he creates his own password in our app.
     * Only useful if there is a Kwf_User_Auth_Interface_Password
     */
    public function allowPasswordForUser(Kwf_Model_Row_Interface $user);
}
