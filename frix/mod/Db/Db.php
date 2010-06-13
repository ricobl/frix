<?
class DbException extends Exception {};

// Database abstract class to be used as a base for database drivers classes
abstract class Db {
	
	// Connection holder
	protected $link;
	
	// Error properties
	protected $errno;
	protected $error;
	
	// Stats
	static $last_query;
	static $queries = array();
	
	static $instance;
	
	/////////////////////////////
	/* DRIVER SPECIFIC METHODS */
	/////////////////////////////
	
	// Connects and selects database
	abstract function connect ($params);
	// Load error code and description
	abstract function load_error ();
	// Read next Auto-increment value from the table properties
	abstract function next_id ($table);
	// Read last inserted Auto-increment value
	abstract function last_id ();
	// Return the number of rows of a resource
	abstract function num_rows ($res);
	// Read the current row as an associative array
	abstract function fetch_assoc ($res);
	// Free the query resource
	abstract function free ($res);
	
	// Create the database object auto-selecting the driver
	static function create ($dsn) {
		
		$params = parse_url($dsn);
		
		// Remove the "/" before the DB_NAME
		$params['path'] = substr($params['path'], 1);
		
		$driver_name = 'Db' . ucfirst($params['scheme']);
		
		load('Db/' . $driver_name);
		
		self::$instance = new $driver_name;
		self::$instance->connect($params);
		
		return self::$instance;
		
	}
	
	static function get_instance () {
		
		if (!self::$instance) {
			throw new DbException('Database not connected!');
		}
		
		return self::$instance;
		
	}
	
	// Prepares an value to be safely used on a SQL command
	static function escape_string ($value) {
		
		// Opening and closing quotes + M and N dashes
		// 145 146 147 148 150 151
		$chr = '‘’“”–—';
		
		// Simple quotes and dashes
		$ent = '\'\'""--';
		
		// Replace characters by its "html-friendly" correspondents
		// *** strtr proved more effcient than str_replace
		$value = strtr($value, $chr, $ent);
		
		// Stripslashes (in case of magic quotes on)
		if (get_magic_quotes_gpc()) {
			$value = stripslashes($value);
		}
		
		return $value;		
		
	}
	
	// Escapes a table or field name to be used on a query
	static function escape_name ($name) {
		return sprintf('`%s`', $name);
	}
	
	// Checks for an error
	function check_error () {
		$this->load_error();
		if ($this->errno) {
			$this->error(sprintf('[%d]: %s (%s)', $this->errno, $this->error, Db::$last_query));
			// $this->error(sprintf('[%d]: %s', $this->errno, $this->error));
		}
		return $this->errno;
	}
	
	// Generates an error
	function error ($msg) {
		throw new DbException($msg);
	}
	
	function query ($string, $params = null) {
		
		// Params not an array?
		if (!is_array($params)) {
			// Take function arguments and remove the first (SQL string)
			$params = func_get_args();
			$params = array_slice($params, 1);
		}
		
		// Any parameter passed?
		if (count($params)) {
			// Prepare each param
			foreach ($params as $key => $value) {
				$params[$key] = $this->prepare_param($value);
			}
			// Insert the values into the string
			// $string = vsprintf($string, $params) or $this->error('Invalid sprintf: ' . $string ."\n".'Arguments: '. implode(', ', $params));
			$string = vsprintf($string, $params);
		}
		
		
		// Save the query (for debugging)
		Db::$last_query = $string;
		
		// Start time
		$timing = microtime(true);
		// Run the query
		$resource = $this->execute($string, $this->link);
		// Stop time
		$timing = (int)((microtime(true)-$timing)*1000);
		
		// Save the query and time
		Db::$queries[] = array($timing, $string);
		
		// Return resource id
		return $resource;
		
	}
	
	function prepare_param($param) {
		if ($param === null) return 'NULL';
		elseif (is_integer($param)) return $param;
		elseif (is_bool($param)) return $param ? 1 : 0;
		return '"' . $this->escape_string($param) . '"';
	}
	
	// Return an array of rows (associative)
	function fetch ($resource) {
		
		// Start the array
		$data = array();
		
		// Any data found?
		if ($this->num_rows($resource)) {
			// Put the rows in an array
			while ($row = $this->fetch_assoc($resource)) {
				$data[] = $row;
			}
		}
		
		// Free the result
		$this->free($resource);
		
		// Return the array
		return $data;
		
	}
	
}
?>