<?
class App {
	
	public $name;
	public $verbose_name;
	
	public $path;
	public $views_path;
	public $models_path;
	
	private $models;
	private $views;
	
	// public $options;
	
	function __construct ($name) {
		
		$apps = Frix::config('APPS');
		
		// Check if app is installed or throw an exception
		if (!array_key_exists($name, $apps)) {
			throw new AppException(sprintf('Application "%s" not installed!', $name));
		}
		
		// Set name
		$this->name = $name;
		
		// Use the class-name to create verbose_name if it's not set
		$v_name = preg_replace('/App$/', '', get_class($this));
		def($this->verbose_name, $v_name);
		
		// Configure the application path
		// $this->path = join_path(array(Frix::config('ROOT'), $apps[$name]));
		$this->path = $apps[$name];
		
		// Setup models and views path
		def($this->views_path, join_path(array($this->path, 'views.php')));
		def($this->models_path, join_path(array($this->path, 'models')));
		
	}
	
	// Member overloading: getter and setter
	public function __get ($name) {
		$method = 'get_' . $name;
		if (method_exists($this, $method)) {
			return $this->$method($name);
		}
	}
	public function __set ($name, $value) {
		
		$method = 'set_' . $name;
		
		if (method_exists($this, $method)) {
			return $this->$method($name, $value);
		}
		// Allow non-initialized property injection
		else {
			$this->$name = $value;
		}
		
	}
	
	// Property overload for $model: returns an array of available models
	private function get_models () {
		
		if (!isset($this->models)) {
			// Each model must have its own php-file named by its Model-extended class
			$this->models = Fs::dir($this->models_path, '(.+)\.php$', '\1');
		}
		
		return $this->models;
		
	}
	
	// Property overload for $views: returns an instance of a Views object for this app
	private function get_views () {
		
		if (!isset($this->views)) {
			import($this->views_path);
			$this->views = new Views;
		}
		
		return $this->views;
		
	}
	
	public function load_model ($name) {
		/*
		if (!array_key_exists($name, $this->models) {
			return false;
			// TODO: maybe throw an exception here...
			// throw new Exception(sprintf('Model "%s" not available.', $name));
		}
		*/
		
		$ok = import(join_path(array($this->models_path, $name . '.php')));
		
		// Make a shortcut for the app on the model
		ModelMeta::meta($name)->app = $this;
		
		return $ok;
		
	}
	
}
?>
