<?php

// include plugin configuration
Configure::load('OAuth2Server.config');

// include Tim Ridgeley's class
App::import('Vendor', 'OAuth2Server.OAuth2', array('file' => 'oauth2-php'. DS .'lib'. DS .'OAuth2.inc'));

// extend with overloaded customizations
class OAuth2Lib extends OAuth2 {
	/**
	 * Persistent reference to controller invoking this component.
	 */
	var $controller;

	/**
	 * Load a model for use within this object.
	 *
	 * @param String $name Model name
	 */
	private function loadModel($name) {
		$plugin = '';
		if (strstr($name, '.')) { // plugin
			list($plugin, $name) = explode('.', $name);
			$plugin .= '.';
		}
		App::import('Model', $plugin . $name);
		$this->{$name} = new $name();
	}

	/**
	 * Make sure that the client id is valid
	 * If a secret is required, check that they've given the right one
	 * Must return false if the client credentials are invalid
	 */
	public function auth_client_credentials($client_id, $client_secret = null) {
		$this->loadModel('OAuth2Server.OAuth2ServerClient');
		return (boolean) $this->OAuth2ServerClient->field('id', array(
			'id' => $client_id,
			'secret' => $client_secret
		));
	}

	/**
	 * OAuth says we should store request URIs for each registered client
	 * Implement this function to grab the stored URI for a given client id
	 * Must return false if the given client does not exist or is invalid
	 */
	protected function get_redirect_uri($client_id) {
		$this->loadModel('OAuth2Server.OAuth2ServerClient');
		return $this->OAuth2ServerClient->field('redirect_uri', array(
			'id' => $client_id
		));
	}

	/**
	 * We need to store and retrieve access token data as we create and verify tokens
	 * Look up the supplied token id from storage, and return an array like:
	 */
	protected function get_access_token($oauth_token) {
		// cache this request because it can get called a lot
		static $tokens = array();
		if (isset($tokens[$oauth_token])) {
			return $tokens[$oauth_token];
		}

		$this->loadModel('OAuth2Server.OAuth2ServerToken');
		$result = $this->OAuth2ServerToken->find('first', array(
			'fields' => array(
				'token',
				'client_id',
				'expires',
				'scope',
				'username'
			),
			'conditions' => array(
				'token' => $oauth_token
			)
		));
		if ($result) {
			return $tokens[$oauth_token] = $result['OAuth2ServerToken'];
		}
		else {
			return null;
		}
	}

	/**
	 * Store the supplied values
	 */
	protected function store_access_token($oauth_token, $client_id, $expires, $scope = null, $username = null) {
		$this->loadModel('OAuth2Server.OAuth2ServerToken');
		$data = array(
			'token' => $oauth_token,
			'client_id' => $client_id,
			'expires' => $expires,
			'scope' => $scope,
			'username' => $username
		);
		if (isset($_REQUEST['device_id'])) {
			$data['device_id'] = &$_REQUEST['device_id'];
		}
		$this->OAuth2ServerToken->save($data, true, array(
			'token',
			'client_id',
			'expires',
			'scope',
			'username',
			'device_id'
		));
	}

	/**
	 *
	 */
	protected function get_supported_grant_types() {
		return array(
		AUTH_CODE_GRANT_TYPE,
		USER_CREDENTIALS_GRANT_TYPE,
		CLIENT_CREDENTIALS_GRANT_TYPE,
		//ASSERTION_GRANT_TYPE,
		REFRESH_TOKEN_GRANT_TYPE,
		//NONE_GRANT_TYPE
		);
	}

	/**
	 *
	 */
	protected function get_supported_auth_response_types() {
		return array(
		AUTH_CODE_AUTH_RESPONSE_TYPE,
		ACCESS_TOKEN_AUTH_RESPONSE_TYPE,
		CODE_AND_TOKEN_AUTH_RESPONSE_TYPE
		);
	}

	/**
	 *
	 */
	protected function get_supported_scopes() {
		return array();
	}

	/**
	 *
	 */
	protected function authorize_client_response_type($client_id, $response_type) {
		return true;
	}

	/**
	 *
	 */
	protected function authorize_client($client_id, $grant_type) {
		return true;
	}

	/* Functions that help grant access tokens for various grant types */

	/**
	 * Fetch authorization code data (probably the most common grant type)
	 * IETF Draft 4.1.1: http://tools.ietf.org/html/draft-ietf-oauth-v2-08#section-4.1.1
	 * Required for AUTH_CODE_GRANT_TYPE
	 */
	protected function get_stored_auth_code($code) {
		$this->loadModel('OAuth2Server.OAuth2ServerCode');
		$result = $this->OAuth2ServerCode->find('first', array(
			'fields' => array(
				'access_code',
				'client_id',
				'redirect_uri',
				'expires',
				'scope'
				),
			'conditions' => array(
				'access_code' => $code
				)
			));

			if ($result) {
				return array(
					'client_id' => $result[0]['OAuth2ServerCode']['client_id'],
					'redirect_uri' => $result[0]['OAuth2ServerCode']['redirect_uri'],
					'expires' => $result[0]['OAuth2ServerCode']['expires'],
					'scope' => $result[0]['OAuth2ServerCode']['scope']
				);
			}
			else {
				return null;
			}
	}

	/**
	 * Take the provided authorization code values and store them somewhere (db, etc.)
	 * Required for AUTH_CODE_GRANT_TYPE
	 */
	protected function store_auth_code($code, $client_id, $redirect_uri, $expires, $scope = null) {
		$this->loadModel('OAuth2Server.OAuth2ServerCode');
		$this->OAuth2ServerCode->save(array(
			'access_code' => $code,
			'client_id' => $client_id,
			'redirect_uri' => $redirect_uri,
			'expires' => $expires,
			'scope' => $scope
		)) or die('Unknown error saving oauth access code.');
	}

	/**
	 * Grant access tokens for basic user credentials
	 * IETF Draft 4.1.2: http://tools.ietf.org/html/draft-ietf-oauth-v2-08#section-4.1.2
	 * Required for USER_CREDENTIALS_GRANT_TYPE
	 */
	public function check_user_credentials($client_id, $username, $password) {
		if (
			   !empty($username)
			&& !empty($password)
		) {
			// use CakePHP Auth Component to validate user credentials
			$Auth = Configure::read('OAuth2Server.Auth.className');
			$data = array(
				Configure::read('OAuth2Server.Auth.fields.username') => $username,
				Configure::read('OAuth2Server.Auth.fields.password') => $password
			);
			if ($Auth == 'Auth') { // only pre-hash passwords for original Auth component
				$data = $this->controller->$Auth->hashPasswords($data);
			}
			return (boolean) $this->controller->$Auth->identify($data);
		}
		return false;
	}

	/**
	 * Grant refresh access tokens
	 * IETF Draft 4.1.4: http://tools.ietf.org/html/draft-ietf-oauth-v2-08#section-4.1.4
	 * Required for REFRESH_TOKEN_GRANT_TYPE
	 */
	protected function get_refresh_token($refresh_token) {
		// for now, we're storing these in the same way as access tokens
		return $this->get_access_token($refresh_token);
	}

	/**
	 * Store refresh access tokens
	 * Required for REFRESH_TOKEN_GRANT_TYPE
	 */
	protected function store_refresh_token($token, $client_id, $expires, $scope = null, $username = null) {
		// for now, we're storing these in the same way as access tokens
		return $this->store_access_token($token, $client_id, $expires, $scope, $username); // @TODO: infer username from previous token
	}

	/**
	 * Expire a used refresh token.
	 * This is not explicitly required in the spec, but is almost implied. After granting a new refresh token,
	 * the old one is no longer useful and so should be forcibly expired in the data store so it can't be used again.
	 */
	public function expire_refresh_token($token) {
		$this->loadModel('OAuth2Server.OAuth2ServerToken');
		$this->OAuth2ServerToken->delete($token) or die('failed to expire refresh token.');
	}

	/**
	 *
	 */
	protected function get_default_authentication_realm() {
		return 'API Server';
	}

	/**
	 * Get full token record from database,
	 * matching by access_token.
	 *
	 * @return Array Token record.
	 */
	public function get_token() {
		$token_param = $this->get_access_token_param();
		return $this->get_access_token($token_param);
	}

	/**
	 * Get individual field value from User record in database, 
	 * matching by access_token.
	 *
	 * @return Array User record.
	 */
	public function get_token_user($field) {
		$token = $this->get_token();
		if ($token !== null && !empty($token['username'])) {
			$this->loadModel('User');
			return $this->User->field($field, array(
			  Configure::read('OAuth2Server.Auth.fields.username') => $token['username']
			));
		}
	}
}