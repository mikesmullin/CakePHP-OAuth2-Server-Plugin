<?php

echo
	$this->Form->create(null, array('type' => 'POST', 'url' => '/oauth/authorize')) .
	$this->Form->inputs(array(
		'legend' => __('User login', true),
		'client_id' => array(
			'name' => 'client_id',
			'type' => 'hidden',
			'value' => isset($this->params['url']['client_id'])? $this->params['url']['client_id'] : '',
		),
		'response_type' => array(
			'name' => 'response_type',
			'type' => 'hidden',
			'value' => isset($this->params['url']['response_type'])? $this->params['url']['response_type'] : '',
		),
		'redirect_uri' => array(
			'name' => 'redirect_uri',
			'type' => 'hidden',
			'value' => isset($this->params['url']['redirect_uri'])? $this->params['url']['redirect_uri'] : '',
		),
		'state' => array(
			'name' => 'state',
			'type' => 'hidden',
			'value' => isset($this->params['url']['state'])? $this->params['url']['state'] : '',
		),
		'scope' => array(
			'name' => 'scope',
			'type' => 'hidden',
			'value' => isset($this->params['url']['scope'])? $this->params['url']['scope'] : '',
		),
		'device_id' => array(
			'name' => 'device_id',
			'type' => 'hidden',
			'value' => isset($this->params['url']['device_id'])? $this->params['url']['device_id'] : '',
		),
		'username' => array(
			'name' => 'username',
			'type' => 'text',
			'label' => __('Username:', true),
		),
		'password' => array(
			'name' => 'password',
			'type' => 'password',
			'label' => __('Password:', true),
		)
	)) .
	$this->Form->end(__('Login', true))
	;
