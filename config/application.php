<?php
	// Debugging Mode
	$_CONFIG['debug'] = true;
	
	// App-wide timezone
	$_CONFIG['timezone'] = 'Australia/Sydney';

	ActiveRecord\Config::initialize(function($cfg)
	{
	    $cfg->set_model_directory('app/models/');
	    $cfg->set_connections(array('development' => 'mysql://root@127.0.0.1/database'));
	});
?>