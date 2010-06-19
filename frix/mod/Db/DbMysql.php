<?
class DbMysql extends Db {
	
	// Connects and selects database
	function connect ($params) {
		$this->link = mysql_connect($params['host'], $params['user'], $params['pass']);
		$this->check_error();
		mysql_select_db($params['path'], $this->link);
	}
	
	// Load error code and description
	function load_error () {
		// Load error number
		$this->errno = mysql_errno($this->link);
		// Load error description
		$this->error = mysql_error($this->link);
	}
	
	// Return a result identifier
	function execute ($sql) {
		$resource = mysql_query($sql);
		$this->check_error();
		return $resource;
	}
	
	// Prepares an value to be safely used on a SQL command
	static function escape_string ($value) {
		// Escape the string for MySQL
		return mysql_real_escape_string(Db::escape_string($value));
	}
	
	// Return the number of rows of a resource
	function num_rows ($res) {
		return mysql_num_rows($res);
	}
	
	// Read the current row as an associative array
	function fetch_assoc ($res) {
		return mysql_fetch_assoc($res);
	}
	
	// Free the query resource
	function free ($res) {
		return mysql_free_result($res);
	}
	
	// Read next Auto-increment value from the table properties
	function next_id ($table) {
		$res = $this->execute($this->query('SHOW TABLE STATUS LIKE "%s"', $table));
		$row = $this->fetch($res);
		return $row[0]['Auto_increment'];
	}
	
	// Read last inserted Auto-increment value
	function last_id () {
		// return $this->fetch_value('SELECT LAST_INSERT_ID()');
		return mysql_insert_id($this->link);
	}
	
}
?>
