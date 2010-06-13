<?
class AdminApp extends App {
	
	public $registry = array();
	
	function __construct ($name) {
		parent::__construct($name);
		// Automatically load AdminOptions
		$this->load_options();
	}
	
	// Speed up things by returning null until we have models here
	private function get_models () {
		return null;
	}
	
	// Load AdminOptions module
	function load_options () {
		require_once('AdminOptions.php');
	}
	
	function register ($model, $options = 'AdminOptions') {
		
		$opt = new $options($model);
		// Get app name from model
		$app_name = $opt->meta->app->name;
		
		// Save model URL
		$opt->meta->admin_url = join_path(array($app_name, uncamel($model)), '/');
		
		// Register the entry grouping by app
		// Start app array if not found
		if (!$this->registry[$app_name]) {
			$this->registry[$app_name] = array($opt->get_name() => $opt);
		}
		// Or include the new entry
		else {
			$this->registry[$app_name][$opt->get_name()] = $opt;
		}
		
	}
	
	
}
?>