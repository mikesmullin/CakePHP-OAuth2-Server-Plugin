<?php

// include plugin configuration
Configure::load('OAuth2Server.config');

class OAuth2ServerAppController extends AppController {
	var $components = array('OAuth2');
	var $helpers = array();

	/**
	 * Dynamically set Auth component.
	 */
	function __construct() {
		parent::__construct();
		$this->components[] = Configure::read('OAuth2Server.Auth.className');
	}

	/**
	 * beforeFilter() callback.
	 * Configure Auth component.
	 */
	function beforeFilter() {
		$Auth = Configure::read('OAuth2Server.Auth.className');
		$this->$Auth->deny('*');

		foreach (array_merge(array(
			'loginAction' => array(
				'plugin' => false,
				'admin' => false,
				'controller' => 'oauth',
				'action' => 'login'
			),
			'autoRedirect' => false,
			'authorize' => 'controller',
			'allowedActions' => array('login', 'authorize', 'access_token')
		), Configure::read('OAuth2Server.Auth')) as $k => $v) {
			$this->$Auth->{$k} = $v;
		}

		return parent::beforeFilter(); // bubble up
	}

	/**
	 * Notify client of internal error and die.
	 *
	 * @param Exception $exception Exception object.
	 */
	function fail($exception) {
		error_reporting(0);
		Configure::write('debug', 0);
		header('HTTP/1.1 500 Internal Error', true);
		header('Pragma: no-cache');
		header('Cache-Control: no-store, no-cache, max-age=0, must-revalidate');
		header('Content-Type: text/javascript');
		App::import('Helper', 'Javascript');
		$javascript = new JavascriptHelper();
		die($javascript->object(array('error' => array(
			'type' => get_class($exception),
			'message' => $exception->getMessage()
		))));
	}
}