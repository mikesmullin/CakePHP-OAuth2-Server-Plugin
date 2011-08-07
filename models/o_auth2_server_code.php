<?php

class OAuth2ServerCode extends AppModel {
	var $name = 'OAuth2ServerCode';
	var $primaryKey = 'access_code';

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