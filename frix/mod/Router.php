<?
class RouterException extends Exception {};
class RouterNoRoutesException extends RouterException {};
class RouterNoViewException extends RouterException {};

class Router {
	
	public $command;
	
	public $app;
	public $app_name;
	public $view_name;
	public $params;
	
	function __construct ($routes, $command) {
		
		// Routes to process
		$this->routes = $routes;
		
		// Add a slash to the end of the command path in case it's not found
		if (substr($command, -1) != '/') {
			$command .= '/';
		}
		
		// Starting URL command
		$this->command = $command;
		
	}
	
	function start () {
		
		// Check if the routes are defined
		if (!$this->routes) {
			throw new RouterNoRoutesException('No routes defined.');
		}
		
		// Check for routes config
		if ($this->find_route()) {
			// Run the selected view
			return $this->run_view();
		}
		
		// No success routing to a view: throw a 404 error
		throw new Http404Exception;
		
	}
	
	function find_route () {
		
		// Loop through every route
		foreach ($this->routes as $route => $view_path) {
			
			// Test if the RegEx match the command
			if ($this->check_route($route)) {
				
				// Break the command to get an application name and a view
				list($this->app_name, $this->view_name) = explode('/', $view_path);
				
				// Found a match, no more need for testing
				return true;
				
			}
		}
		
		return false;
		
	}
	
	function check_route ($route) {
		
		$matches = null;
		
		if (preg_match('#' . $route . '#', $this->command, $matches)) {
			
			// Remove the first RegEx match (full match) and save params for later
			array_shift($matches);
			$this->params = $matches;
			
			return true;
			
		}
		
		return false;
		
	}
	
	function run_view () {
		
		// Get an instance of an app and load the views
		$this->app = Frix::app($this->app_name);
		
		// Check if the class is valid and the method exists
		if (method_exists($this->app->views, $this->view_name)) {
			
			// Call the view method passing params found by check_route
			call_user_func_array(array(&$this->app->views, $this->view_name), $this->params);
			
			// Return success
			return true;
			
		}
		
		// No view found, throw an error!
		throw new RouterNoViewException(sprintf('View "%s" not found on app "%s".', $this->view_name, $this->app_name));
		
	}
}
?>
