<?php

// GET /oauth/login
Router::connect('/oauth/login', array('plugin' => 'o_auth2_server', 'controller' => 'o_auth2_server', 'action' => 'login', '[method]' => 'GET'));

// POST /oauth/authorize
Router::connect('/oauth/authorize', array('plugin' => 'o_auth2_server', 'controller' => 'o_auth2_server', 'action' => 'authorize', '[method]' => 'POST'));

// POST /oauth/access_token
Router::connect('/oauth/access_token', array('plugin' => 'o_auth2_server', 'controller' => 'o_auth2_server', 'action' => 'access_token', '[method]' => 'POST'));