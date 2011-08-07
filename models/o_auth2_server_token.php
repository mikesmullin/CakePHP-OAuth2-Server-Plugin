<?php

class OAuth2ServerToken extends AppModel {
	var $name = 'OAuth2ServerToken';
	var $primaryKey = 'token';

	var $belongsTo = array(
		'OAuth2ServerClient' => array(
			'className' => 'OAuth2ServerClient',
			'foreignKey' => 'client_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}