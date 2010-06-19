<?
class HttpException extends Exception {};

class Http404Exception extends HttpException {
	function __construct ($message = null, $oode = 404) {
		$message = sprintf('Page "%s" not found.', $_SERVER['REQUEST_URI']);
		parent::__construct($message, $code);
	}

};

class RequestHandler {
	
	var $command;
	var $routes;
	
	function __construct ($routes, $command) {
		
		// Routes to process
		$this->routes = $routes;
		
		// Starting URL command
		$this->command = $command;
		
	}
	
	function start () {
		
		// Output-buffering: ON
		ob_start();
		
		// Pre-view middleware
		// ### TODO ###
		
		// Use the Router to map the command string to a view
		try {
			$router = new Router($this->routes, $this->command);
			$router->start();
		}
		catch (Http404Exception $e) {
			return self::http_404($e->getMessage());
		}
		catch (Exception $e) {
			return self::http_500($e->getMessage());
		}
		
		// Post-view middleware
		// ### TODO ###
		
		// Output-buffering: Flush
		ob_end_flush();
		
	}
	
	static function http_404 ($error_msg) {
		
		header('HTTP/1.0 404 Not Found');
		
		$t = new Template('http_404', 'frix/http_404');
		echo $t->render(array('error_msg' => $error_msg, 'error_code' => 404));
		
	}
	
	static function http_500 ($error_msg) {
		
		header('HTTP/1.1 500 Internal Server Error'); 
		
		$t = new Template('http_500', 'frix/http_500');
		echo $t->render(array('error_msg' => $error_msg, 'error_code' => 500));
		
	}
	
}
?>
