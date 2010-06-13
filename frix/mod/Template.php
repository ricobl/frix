<?
class TemplateException extends Exception {};

load('Html');

class Template {
	
	public $context;
	
	// Template instances cache
	// To know which template is currently being rendered
	private static $instances = array();
	
	// Template blocks properties
	// Last opened block
	private $last_block;
	// Block stack
	private $blocks = array();
	// Extended template flag
	private $extending = false;

	// Constructor, expects a template identifier
	// May be a string: 'news/base'
	// Or an array: array('news/special', 'news/base')
	function __construct () {
		
		$args = func_get_args();
		
		if (is_array($args[0])) {
			$this->template = $args[0];
		}
		else {
			$this->template = $args;
		}
		
	}
	
	// Get the currently rendering template
	static function current () {
		return end(self::$instances);
	}
	
	// Return an array of attributes in $k="$v" format
	static function attrs ($attrs) {
		
		if (!$attrs) {
			return;
		}
		
		foreach ($attrs as $k => $v) {
			$str_attrs .= sprintf(' %s="%s"', $k, htmlentities($v));
		}
		
		return $str_attrs;
		
	}
	
	// Finds an existent template file
	// Receives a string or array of strings
	// Returns a file path
	function find_template ($template) {
		
		// Template param may be a string or an array
		// If it's not an array, put the string in a new array
		if (!is_array($template)) {
			$template = array($template);
		}
		
		// Loop through template paths config
		foreach (Frix::config('TEMPLATE_PATHS') as $path) {
			
			foreach ($template as $template_file) {
				
				$template_path = join_path(array($path, $template_file . '.php'));
				
				if (file_exists($template_path)) {
					return $template_path;
				}
				
			}
			
		}
		
		// No template found? Throw an exception.
		// debug($template);
		throw new TemplateException(sprintf('Template(s) "%s" not found!', implode('", "', $template)));
		
	}
	
	// Render the template using a context
	function render ($context = array()) {
		
		// Save the context (for extendable templates)
		$this->context = $context;
		
		// Get template file
		$template_file = $this->find_template($this->template);
		
		// File found?
		if ($template_file) {
			
			// Extract context into the scope, before processing the template
			extract($this->context);
			
			// Add current template to the instances array
			self::$instances[] = $this;
			
			// Start output buffering
			ob_start();
			
			// Render the file
			include($template_file);
			
			// Get non-block contents
			$this->get_non_block();
			
			// Clear and finish output buffering
			ob_end_clean();
			
			// Remove current template from the instances array
			array_pop(self::$instances);
			
			// Return rendered contents
			return implode('', $this->blocks);
			
		}
		
	}
	
	/////////////////////////////////
	/* Extended templates handling */
	/////////////////////////////////
	
	// Get non-blocked content as an anonymous block
	function get_non_block () {
		
		// Not extending a template?
		if (!$this->extending) {
			// Save non-blocked content
			$this->blocks[] = ob_get_contents();
		}
		
		// Clean output
		ob_clean();
		
	}

	// Start parsing a block
	function block ($name) {
		
		// Save block name for 'end_block'
		$this->last_block = $name;
		
		$this->get_non_block();
		
	}
	
	// Return current block parent contents
	function super () {
		return $this->blocks[$this->last_block];
	}
	
	// Close the last opened block
	function end_block () {
		
		// Save block contents and clean output
		$this->blocks[$this->last_block] = ob_get_contents();
		ob_clean();
		
	}
	
	// Extends another template
	function extend () {
		
		$args = func_get_args();
		
		// Find template file
		$template_file = $this->find_template($args);
		
		// File found?
		if ($template_file) {
			
			// Extract context
			extract($this->context);
			
			// Render the template
			include($template_file);
			
			// Get non-block
			$this->get_non_block();
			
			// Let blocks know they're extended
			// To avoid capturing non-blocked contents in child templates
			$this->extending = true;
			
		}
		
	}

}

////////////////////////
/* Template shortcuts */
////////////////////////

function extend () {
	$curr = Template::current();
	$args = func_get_args();
	call_user_func_array(array($curr, 'extend'), $args);
}
function super () {
	return Template::current()->super();
}
function block ($name) {
	return Template::current()->block($name);
}
function end_block () {
	return Template::current()->end_block();
}
?>