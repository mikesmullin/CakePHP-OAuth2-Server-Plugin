<?php
class M4e3da3fa4ebc45749f8c0e391506eaf2 extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 * @access public
 */
	public $description = 'Create OAuth2 Server schema.';

/**
 * Actions to be performed
 *
 * @var array $migration
 * @access public
 */
	public $migration = array(
		'up' => array(
			'create_table' => array(
				'o_auth2_server_clients' => array(
					'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 20, 'key' => 'primary'),
					'secret' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 20),
					'redirect_uri' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 200),
					'description' => array('type' => 'string', 'null' => true, 'default' => NULL),
					'indexes' => array(
						'PRIMARY' => array('column' => 'id', 'unique' => 1),
					),
					'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM'),
				),
				'o_auth2_server_codes' => array(
					'access_code' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 40, 'key' => 'primary'),
					'client_id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 20),
					'redirect_uri' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 200),
					'expires' => array('type' => 'integer', 'null' => false, 'default' => NULL),
					'scope' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 250),
					'indexes' => array(
						'PRIMARY' => array('column' => 'access_code', 'unique' => 1),
					),
					'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM'),
				),
				'o_auth2_server_tokens' => array(
					'token' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 40, 'key' => 'primary'),
					'client_id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 20),
					'expires' => array('type' => 'integer', 'null' => false, 'default' => NULL),
					'scope' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 200),
					'username' => array('type' => 'string', 'null' => true, 'default' => NULL),
					'device_id' => array('type' => 'string', 'null' => true, 'default' => NULL),
					'indexes' => array(
						'PRIMARY' => array('column' => 'token', 'unique' => 1),
					),
					'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM'),
				),
			),
		),
		'down' => array(
			'drop_table' => array(
				'o_auth2_server_clients', 'o_auth2_server_codes', 'o_auth2_server_tokens'
			),
		),
	);

/**
 * Before migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return boolean Should process continue
 * @access public
 */
	public function before($direction) {
		return true;
	}

/**
 * After migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return boolean Should process continue
 * @access public
 */
	public function after($direction) {
		$Query = $this->generateModel('Query', false, array('table' => false));
		switch ($direction) {
			case 'up':
				return $Query->query(<<<MYSQL

INSERT INTO `o_auth2_server_clients` (`id`, `secret`, `redirect_uri`, `description`)
VALUES
 ('test', 'test', '', 'Developer Sandbox'),
 ('F4lnYzOteELJRKcWdKkG', 'r52745C8B7K351d71nw0', '', 'Web Application'),
 ('DERUHwrBIDpc6fj2yys3', 'LU404t1Ul4qknIdVbwP4', '', 'iPhone Application'),
 ('dSf8S9k4N3675I5HE63A', 'r58q7d4I7Fs3UKW152Xo', '', 'Android Application')
;

MYSQL
				);
				break;

			case 'down':
				break;
		}
		return true;
	}
}
?>