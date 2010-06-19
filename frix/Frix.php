<?
/*
== CORE MODULE ==

This module is used to encapsulate all the main functionalities like:

- configuration;
- path resolving;
- modules and classes loading;

It is preferred to have only static methods and properties, to avoid instantiation.

= CONFIG =

Use this class to access global configuration and to your own FRIX project.
*/

class Frix {
	
	static $db;
	static $apps = array();
	static $routes = array();
	static $template_paths = array();
	
	private static $app_cache = array();
	
	private static $config = array();
	
	// Recursively read each param as a key from the configuration
		// Frix::config('APPS')
		// Frix::config('APPS', 'cms')
	static function config () {
		$conf =& self::$config;
		$args = func_get_args();
		foreach ($args as $arg) {
			$conf =& $conf[$arg];
		}
		return $conf;
	}
	
	// Load configuration values from a file
	static function load_config ($file) {
		// Make current config available as reference to the included file
		$config =& self::$config;
		// Load the config file (expecting an $config array to be populated...)
		@include_once($file);
	}
	
	static function start ($config_file = null) {
		
		// Default config file, default path: the Frix dir
		self::load_config( join_path(array(dirname(__FILE__), 'default_config.php')) );
		
		// Load user config file
		// Default config file path: same dir of the root 'index.php' file
		self::load_config($config_file ? $config_file : join_path(dirname($_SERVER['SCRIPT_FILENAME']), 'config.php'));
		
		// Import commonly used modules
		load('RequestHandler');
		load('Router');
		load('Fs');
		load('App');
		load('Db/Db');
		load('Field');
		load('Db/Model');
		load('Db/Query');
		load('Template');
		
		// Create a database connection
		Db::create(Frix::config('DB_URL'));
		
		// Use PATH_INFO to feed the request handler
		$path = $_SERVER['PATH_INFO'];
		
		// Make sure the path ends with a '/'
		if ($path[strlen($path)-1] != '/') {
			// If not POSTing, fix the path and redirect
			if ($_SERVER['REQUEST_METHOD'] != 'POST') {
				redir(url(Frix::config('WEB_ROOT'), $path));
			}
			
			// Otherwise, just fix the path
			$path = url($path);
		}
		
		// Create a request handler
		$handler = new RequestHandler(Frix::config('ROUTES'), $path);
		
		// Start processing the request
		$handler->start();
		
	}
	
	// Loads an App module
	static function app ($app_name) {
		
		if (!array_key_exists($app_name, Frix::$app_cache)) {
			
			// Import app definition file
			import(join_path(array(Frix::config('APPS', $app_name), 'app.php')));
			
			// Convert app-name to app-classname: news -> NewsApp
			// $app_class = ucfirst(strtolower($app_name)) . 'App';
			$app_class = camel($app_name) . 'App';
			
			// Create a new instance of the app in the cache
			Frix::$app_cache[$app_name] = new $app_class($app_name);
			
		}
		
		// Return the app from the cache
		return Frix::$app_cache[$app_name];
		
	}
	
	// Loads an Model module
	static function model ($app_name, $name) {
		Frix::app($app_name)->load_model($name);
	}
	
}
?>
