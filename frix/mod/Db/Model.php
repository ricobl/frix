<?
class ModelValidationException extends Exception {};

function escape ($string) {
	return sprintf('`%s`', $string);
}

class ModelMeta {
	
	static $cache = array();
	
	// Primary key name
	// TODO: allow other PK names
	public $pk = 'id';
	
	// Shortcut for the application
	public $app;
	
	public $name;
	public $model;
	public $table;
	public $table_safe;
	public $verbose_name;
	public $verbose_name_plural;
	
	// Default ordering
	public $ordering = array('id', 'ASC');
	
	public $defaults = array();
	public $validations = array();
	
	function __construct ($model_name) {
		
		// Create a model instance
		$this->model = new $model_name;
		$this->name = $model_name;
		
		// Let the model change meta properties
		$this->model->setup($this);
		
		// Populate default properties
		def($this->table, uncamel($this->name));
		def($this->verbose_name, uncamel($this->name, ' '));
		def($this->verbose_name_plural, $this->verbose_name . 's');
		
		// Apply table prefix from config, to allow single-database sharing between projects
		$this->table = Frix::config('DB_PREFIX') . $this->table;
		
		$this->table_safe = escape($this->table);
		
	}
	
	public function query () {
		$q = new Query($this);
		$q->order_by($this->ordering[0], $this->ordering[1]);
		return $q;
	}
	
	public function __call ($method, $args) {
		/* Try to pass unknown methods to a Query object */
		if (method_exists('Query', $method)) {
			$q = $this->query();
			return call_user_func_array(array($q, $method), $args);
		}
	}
	
	static function meta ($model_name) {
		if (!self::$cache[$model_name]) {
			self::$cache[$model_name] = new ModelMeta($model_name);
		}
		return self::$cache[$model_name];
	}
	
	public function get_app () {
		return self::$app;
	}
	
	/*
	// Sets attribute defaults based on ``defaults`` dict
	function get_defaults () {
		
		$model = $this->model;
		
		foreach ($this->defaults as $field => $value) {
			// Is value a method of the model?
			if (method_exists($model, $value)) {
				// Set the field value as the method return value
				$model->$field = $model->$value();
			}
			// Is value a callable?
			elseif (is_callable($value)) {
				$model->$field = call_user_func($value);
			}
			// A simple value
			else {
				// Set the field value
				$model->$field = $value;
			}
		}
		
	}
	*/
}
   
class Model {
	
	public $fields = array();
	
	// Mark as a new record (use INSERT when saving, or UPDATE if false)
	public $new_record = true;
	
	function __construct ($values = array()) {
		/* Allows setting of fields using values */
		
		// Get default values
		// $this->meta()->get_defaults();
		
		// Create ID field
		$this->add_field('id', new AutoField('Id'));
		
		// Initialize fields
		$this->create_fields();
		
		// Populate field values
		if ($values) {
			// TODO: use a LOAD function here, the same will be used for loading from DATABASE
			foreach ($values as $name => $field) {
				if ($this->fields[$name]) {
					$this->fields[$name]->value = $values[$name];
				}
			}
		}
		
		// Call the custom init method
		$this->init();
		
	}
	
	function create_fields () {
		return;
	}
	function setup ($meta) {
		return;
	}
	function init () {
		return;
	}
	
	function add_field ($name, &$field) {
		
		if (!$name || !$field) {
			return;
		}
		
		// Let the field know his parent
		$field->model = $this;
		
		// Save field name
		$field->name = $name;
		
		// Register the field
		$this->fields[$name] = $field;
	}
	function add_fields ($fields) {
		foreach ($fields as $name => $field) {
			$this->add_field($name, $field);
		}
	}
	
	function __set ($name, $value) {
		if (array_key_exists($name, $this->fields)) {
			$this->fields[$name]->value = $value;
		}
		// Allow undeclared attribute injection
		$this->$name = $value;
	}
	function __get ($name) {
		if (array_key_exists($name, $this->fields)) {
			return $this->fields[$name]->value;
		}
		return $this->$name;
	}
	
	function get_pk () {
		/* Returns value of primary key */
		
		// Get primary key name
		$pk_name = $this->meta()->pk;
		
		// Return PK value
		return $this->$pk_name;
	}
	private function set_pk ($value) {
		/* Sets the primary key */
		
		// Get primary key name
		$pk_name = $this->meta()->pk;
		
		// Set PK value
		$this->$pk_name = $value;
	}
	
	private function insert () {
		/* Uses SQL INSERT to create new record */
		
		$keys = array();
		$values = array();
		foreach ($this->fields as $name => $field) {
			
			// Ignore field if it's an AutoField
			if (is_a($field, 'AutoField')) {
				continue;
			}
			
			$keys[$name] = '%s';
			$values[] = $field->to_db();
			$field->changed = false;
		}
		
		$query = sprintf('INSERT INTO %s (%s) VALUES (%s)',
			$this->meta()->table_safe,
			implode(', ', array_keys($keys)),
			implode(', ', $keys)
		);
		
		Query::raw_sql($query, $values);
		
		$this->set_pk(Db::get_instance()->last_id());
		
		return true;
		
	}
	
	private function update () {
		/* Uses SQL UPDATE to update record */
		
		$keys = array();
		$values = array();
		
		foreach ($this->fields as $name => $field) {
			if ($field->changed) {
				$keys[] = sprintf('%s=%%s', escape($name));
				$values[] = $field->to_db();
				$field->changed = false;
			}
		}
		
		if (!$values) {
			return false;
		}
		
		$query = sprintf('UPDATE %s SET %s WHERE %s=%%s',
			$this->meta()->table_safe,
			implode(',', $keys),
			escape($this->meta()->pk)
		);
		
		$values[] = $this->get_pk();
		
		Query::raw_sql($query, $values);
		
		return true;
		
	}
	
	function is_valid () {
		/* Returns boolean on whether all ``validations`` pass */
		try {
			$this->validate();
			return true;
		}
		catch (ModelValidationException $e) {
			return false;
		}
	}
	
	function validate () {
		/* Tests all ``validations``, throw news ``$Model->ModelValidationException`` */
		
		foreach ($this->meta()->validations as $field => $validator) {
			
			// Model have the validator as a method?
			if (method_exists($this, $validator)) {
				$validator = array($this, $validator);
			}
			// Validator isn't callable?
			elseif (!is_callable($validator)) {
				// Raise an exception for invalid validator
				throw new ModelValidationException(sprintf('Invalid validator for field "%s"', $field));
			}
			
			// At this point validator may be a callable-array or a global function
			// So test field value against the validator
			if (!call_user_func($validator, $this->fields[$field]->value)) {
				throw new ModelValidationException(sprintf('Improper value "%s" for "%s"', $value, $k));
			}
			
		}
		
	}
	
	function save () {
		/* Validates and inserts into or updates database */
		
		$this->validate();
		
		if ($this->new_record) {
			$this->insert();
			$this->new_record = false;
			return true;
		}
		else {
			return $this->update();
		}
	}
	
	function delete () {
		/* Deletes record from database */
		
		$query = sprintf('DELETE FROM %s WHERE %s = %%s LIMIT 1', $this->meta()->table_safe, $this->meta()->pk);
		$values = array($this->get_pk());
		
		Query::raw_sql($query, $values);
		
		return true;
	}
	
	static function meta () {
		// return ModelMeta::meta(get_class());
		return false;
	}
	
	// Reads an associative array and populate field values from database
	function from_db ($row) {
		
		// Fill field values
		foreach ($this->fields as $k => $field) {
			$field->value = $field->from_db($row[$k]);
			$field->changed = false;
		}
		
		// Save PK in case we're changing the ID
		// $this->pk = $this->get_pk();
		
		return $this;
		
	}
	
	// Returns an associative array of field values
	function values () {
		$vals = array();
		foreach ($this->fields as $name => $field) {
			$vals[$name] = $field->value;
		}
		return $vals;
	}

}
?>
