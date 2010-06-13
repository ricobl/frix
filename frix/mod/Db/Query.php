<?
class Query {
	
	public $meta;
	public $type;
	public $conditions = array();
	public $order = '';
	public $limit = array();
	public $cache = array();
	
	function __construct ($meta, $query_type='SELECT *') {
		$this->meta = $meta;
		$this->type = $query_type;
		
		$this->order_by($this->meta->pk);
	}
	
	function create ($data, $new_record = true) {
		
		// Get model name
		$model_name = $this->meta->name;
		
		// Create a model instance
		$instance = new $model_name;
		// Parse values from Db and populate fields
		$instance->from_db($data);
		// Mark new record flag
		$instance->new_record = $new_record;
		
		return $instance;
		
	}
	
	function limit ($i, $count = null) {
		// Query evaluated?
		if ($this->cache) {
			// No end limit param?
			if (!$count) {
				// Return a single value $i
				return $this->cache[$i];
			}
			// Slice from $i until $i+$j and return
			return array_slice($this->cache, $i, $count);
		}
		
		// No count param?
		if (!$count) {
			// Apply the limit to get a single object $i
			$this->limit = array(0, $i);
		}
		else {
			// Limit from $i until $i+$count
			$this->limit = array($i, $count);
		}
		
		return $this;
	}
	
	function count () {
		if (!$this->cache) {
			// Change query type to count the rows
			$this->type = 'SELECT COUNT(*)';
			// Read query data
			$data = $this->fetch($this->execute_query());
			// Return the first value of the first row
			return reset($data[0]);
		}
		else {
			return count($this->cache);
		}
	}
	
	// Filter a query
		// TODO:
			// Accepts an array of conditions in the form: $q->filter(array('field_1', 'value_1', 'field_2', 'value_2'))
			// Or an inline parameter-pairs: $q->filter(array('field_1', 'value_1', 'field_2', 'value_2'))
	function filter ($conditions = array()) {
		if ($conditions) {
			$this->conditions[] = $conditions;
		}
		// Enable chain
		return $this;
	}
	
	function order_by ($field, $direction = 'ASC') {
		$this->order = sprintf('ORDER BY %s %s', escape($field), $direction);
		// Enable chain
		return $this;
	}
	
	function extract_condition_keys () {
		if ($this->conditions) {
			$conds = array();
			foreach ($this->conditions as $cond) {
				if (reset($cond) === null) {
					$conds[] = sprintf('%s IS %%s', key($cond));
				}
				else {
					$conds[] = sprintf('%s=%%s', key($cond));
				}
			}
			return sprintf('WHERE %s', implode(' AND ', $conds));
		}
		return '';
	}
	
	function extract_condition_values () {
		$values = array();
		if ($this->conditions) {
			foreach ($this->conditions as $cond) {
				$values[] = reset($cond);
			}
		}
		return $values;
	}
	
	function extract_limit () {
		if ($this->limit) {
			return sprintf('LIMIT %s', implode(',', $this->limit));
		}
		return '';
	}
	
	function query_template () {
		// echo sprintf('%s FROM %s %s %s %s',
		return sprintf('%s FROM %s %s %s %s',
			$this->type,
			$this->meta->table_safe,
			$this->extract_condition_keys(),
			$this->order,
			$this->extract_limit()
		);
	}
	
	function all ($conditions = array()) {
		// Data not loaded yet?
		if (!$this->cache) {
			// Filter the results
			$this->filter($conditions);
			// Run the query
			$res = $this->execute_query();
			
			// Fetch rows
			foreach ($this->fetch($res) as $row) {
				// Create a model instance with a "not-new" flag
				$instance = $this->create($row, false);
				// Store in the cache
				$this->cache[] = $instance;
			}
		}
		
		return $this->cache;
	}
	
	function one ($conditions = array()) {
		// Limit the query to one result and load data
		$data = $this->limit(1)->all($conditions);
		// TODO: throw not found exception?
		// Return the first element (if any)
		return $data[0];
	}
	
	function execute_query () {
		return Db::get_instance()->query($this->query_template(), $this->extract_condition_values());
	}
	
	function fetch ($res) {
		return Db::get_instance()->fetch($res);
	}
	
	///////////////////
	/* Class Methods */
	///////////////////
	
	static function sql ($sql, $values = array()) {
		$res = self::raw_sql($sql, $values);
		return Db::get_instance()->fetch($res);
	}
	
	static function raw_sql ($sql, $values = array()) {
		return Db::get_instance()->query($sql, $values);
	}
	
}
?>