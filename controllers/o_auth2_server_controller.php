<?php

class OAuth2ServerController extends OAuth2ServerAppController {
	var $name = 'OAuth2Server';
	var $uses = array();

	/**
	 * isAuthorized() callback.
	 * Allow anonymous access to all actions of this controller.
	 */
	function isAuthorized() {
		return true;
	}

	/**
	 * Issue a new access_token to a formerly anonymous user.
	 * Used by apps to authenticate via RESTful APIs.
	 */
	function access_token() {
		try {
			$this->OAuth2Lib->grant_access_token();
		} catch(Exception $e) {
			$this->fail($e);
		}
	}

	/**
	 * Display an HTML login form to end-user.
	 * Used by third-party apps to authenticate via web browser. (Part 1 of 2)
	 */
	function login() {
		$this->helpers[] = 'Form';
	}

	/**
	 * Issue a new access_token to a formerly anonymous user.
	 * Used by third-party apps to authenticate via web browser. (Part 2 of 2)
	 */
	function authorize() {
		try {
			$this->OAuth2Lib->finish_client_authorization(
				(boolean) $this->OAuth2Lib->check_user_credentials($this->params['form']['client_id'], $this->params['form']['username'], $this->params['form']['password']),
				$this->params['form']['response_type'],
				$this->params['form']['client_id'],
				$this->params['form']['redirect_uri'],
				$this->params['form']['state'],
				$this->params['form']['scope'],
				$this->params['form']['username']
			);
		} catch(Exception $e) {
			$this->fail($e);
		}
	}
}