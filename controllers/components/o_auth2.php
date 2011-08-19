<?php

class OAuth2Component extends Object {
	/**
	 * Persistent reference to controller invoking this component.
	 */
	var $controller;

	/**
	 * initialize() callback.
	 * The initialize method is called before the controller's beforeFilter method.
	 */
	function initialize(&$controller, $settings = array()) {
		$this->controller = &$controller;

		// include customized version of third-party class
		App::import('Lib', 'OAuth2Server.OAuth2Lib');
		$controller->OAuth2Lib = new OAuth2Lib(
			Configure::read('OAuth2Server.access_token_lifetime'),
			Configure::read('OAuth2Server.auth_code_lifetime'),
			Configure::read('OAuth2Server.refresh_token_lifetime')
		);
		$controller->OAuth2Lib->controller = &$this->controller; // provide reference to OauthController object

		if (method_exists($controller, 'isAuthorized')) {
			$valid = $controller->isAuthorized();
			switch (true) {
				case $valid === true: // assume valid
					return true;
					break;
				case $valid === false: // assume invalid
					$controller->OAuth2Lib->send_401_unauthorized($realm = null, $scope = null, ERROR_INVALID_TOKEN);
					break;
				default:
				case $valid === null: // check normally
					$controller->OAuth2Lib->verify_access_token();
					break;
			}
		}
		else { // check normally
			$controller->OAuth2Lib->verify_access_token();
		}
	}

	/**
	 * Obtain information about the currently OAuth2 authenticated user.
	 * Similar to AuthComponent::user().
	 *
	 * @param String $field Name of field on User object to return.
	 * @return Mixed Requested data from User object.
	 */
	function user($field) {
		return $this->controller->OAuth2Lib->get_token_user($field);
	}

	/**
	 * Obtain the access_token used by the current user, if any.
	 *
	 * @return String access_token
	 */
	function token() {
		return $this->controller->OAuth2Lib->get_token();
	}

	/**
	 * Obtain the User.id of the currently OAuth2 authenticated user; or,
	 * throw an exception to be caught higher up.
	 *
	 * @param Boolean $throwExceptionOnFail (optional) Whether or not to throw
	 *   an exception if user is not authenticated. Default is TRUE.
	 * @return Integer Current User.id
	 */
	function getCurrentUserId($throwExceptionOnFail = true) {
		// validate and cache to reduce db queries
		static $current_user_id = null;
		if (empty($_REQUEST['access_token'])) { // validate
			if ($throwExceptionOnFail) {
				throw new Exception(__('Missing access_token.', true));
			}
			return false;
		}
		else if ($current_user_id !== null) { // check cache
			return $current_user_id;
		}
		else { // query db
			if ($current_user_id = $this->controller->OAuth2->user('id')) {
				return $current_user_id;
			}
		}
		if ($throwExceptionOnFail) {
			throw new Exception(__('Invalid, expired, or underprivileged access_token.', true));
			return false;
		}
	}
}