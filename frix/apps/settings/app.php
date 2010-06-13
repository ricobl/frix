<?
class SettingsApp extends App {
	
	// Property cache
	static $properties = array();
	
	function __construct ($name) {
		// Call parent constructor
		parent::__construct($name);
		// Auto-load SettingsProperty model
		$this->load_model('SettingsProperty');
	}
	
	function get ($name, $default = null) {
		// Property already loaded?
		if (!array_key_exists($name, self::$properties)) {
			// Load property from the db
			$prop = SettingsProperty::meta()->one(array('name' => $name));
			// Store in the cache
			self::$properties[$name] = $prop ? $prop->value : $default;
		}
		// Return property
		return self::$properties[$name];
	}
	
}
?>