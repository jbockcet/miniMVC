<?php
//
// index.php - The bootup script for the application
// (c) Alem

// Script-timing
$time_start = microtime(true);

// Defines path, DB configurations, etc.
require_once('../config.php');

// Require all lib/ files
foreach ( glob( SERVER_ROOT . DEFAULT_LIBRARY_PATH . '/*.php' ) as $filename){
	require_once( $filename );
}

// Get the controller, method and variable from URL
// each delimited by the forward slash '/'
$parsed_request = explode( '/', $_SERVER['QUERY_STRING'], 3);
$request['controller'] = $parsed_request[0];
$request_size = count( $parsed_request );
if ( $request_size > 1 )
	$request['method'] = $parsed_request[1];
if( $request_size > 2 )
	$request['variable'] = $parsed_request[2];

// Instantiate appropriate controller based on request.
$application = new Controller();
$application -> useController($request);

// Script-timing completion
$time_end = microtime(true);
$time = $time_end - $time_start;
Logger::instantiate() -> record['Script_Time'] = $time;

//Logging Output
Logger::instantiate() -> display();

?>
