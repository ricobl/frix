<?
class FieldException extends Exception {};

class Field {
	
	// Reference props
	public $form;
	public $model;
	private $name;
	
	// Naming props
	public $verbose_name;
	public $verbose_name_plural;
	
	// Db-like props
	public $default;
	public $null;
	public $unique;
	public $index;
	private $value;
	
	// Misc props
	public $editable = true;
	
	// Flag to know if the field value has changed
	public $changed;
	
	// HTML attributes
	public $attrs = array();
	public $type = 'text';
	
	// Other props
	public $options;
	
	function __construct ($verbose_name, $options = array()) {
		
		$this->verbose_name = $verbose_name;
		
		// Loop through field options
		foreach ($options as $k => $v) {
			// Test if the option exists and then assign
			if (property_exists($this, $k)) {
				// Assign option
				$this->$k = $v;
				// Clear property
				unset($options[$k]);
			}
		}
		
		// Assign the remaining options
		$this->options = $options;
		
		// Set 'verbose_name_plural' based on 'verbose_name' if empty
		def($this->verbose_name_plural, $this->verbose_name . 's');
		
		// There's a default value?
		if (isset($this->default)) {
			// Assign it and block the 'changed' flag
			$this->set_value($this->default, false);
		}
		
	}
	
	public function __get ($name) {
		$method = 'get_' . $name;
		if (method_exists($this, $method)) {
			return $this->$method();
		}
		else {
			return $this->$name;
		}
	}
	public function __set ($name, $value) {
		$method = 'set_' . $name;
		if (method_exists($this, $method)) {
			$this->$method($value);
		}
		else {
			$this->$name = $value;
		}
	}
	
	private function set_value ($value, $changed = true) {
		
		$old_value = $this->value;
		
		if ($this->null && ($value === null)) {
			$this->value = null;
		}
		else {
			$this->value = $this->parse($value);
		}
		
		if ($this->value !== $old_value) {
			$this->changed = $changed;
		}
	}
	
	// Parse the field value
	function parse ($value) {
		return $value;
	}
	
	// Parse the value from some input
	// Useful for data conversion like date formats, string normalization, etc.
	function input ($value) {
		return $this->parse($value);
	}
	
	// Parse the value from the database
	// Useful for data conversion
	function from_db ($value) {
		return $this->parse($value);
	}
	
	// Adjust the field value to the Db format
	function to_db () {
		if (!$this->null && ($this->value === null)) {
			return '';
		}
		return $this->value;
	}
	
	function render () {
		
		// Create the template using the class-tree to find a template file
		$t = new Template(get_parent_classes($this, 'frix/form/%s'));
		
		// Set field type
		def($this->attrs['type'], $this->type);
		
		// Set name and id attributes (if not set)
		def($this->attrs['name'], $this->name);
		def($this->attrs['id'], 'id_' . $this->attrs['name']);
		
		// Fill the context values
		$context = array(
			'field' => $this,
		);
		
		// Return the rendered template
		return $t->render($context);
		
	}
	
}

class CharField extends Field  {
	
	public $length;
	
	function parse ($value) {
		if ($value === null) {
			return '';
		}
		return substr($value, 0, $this->length);
	}
	
	function render () {
		$this->attrs['maxlength'] = $this->length;
		return parent::render();
	}
	
}

class PasswordField extends CharField {
	
	public $type = 'password';
	
	function __construct ($name, $options = array()) {
		
		// Block having a default value
		unset($this->default);
		unset($options['default']);
		
		// Call the default constructor
		parent::__construct($name, $options);
		
	}
	
	/*
	// Block accepting external data (like a POST)
	function input ($value) {
		return $this->value;
	}
	*/
	
}

class TextField extends Field  {
}

class IntegerField extends Field  {
	
	function parse ($value) {
		return intval($value);
	}
	
	// Adjust the field value to the Db format
	function to_db () {
		if (!$this->null && ($this->value === null)) {
			return 0;
		}
		return $this->value;
	}
	
}

class AutoField extends IntegerField {
	
	function __construct ($name, $options = array()) {
		
		// Force some properties
		$options['null'] = false;
		$options['editable'] = false;
		
		// Call the default constructor
		parent::__construct($name, $options);
		
	}
	
	// Block rendering, this field can't be edited
	// TODO: block all non-editable fields
	function render () {
		return '';
	}
	
}

class DateField extends CharField  {
	
	public $length = 10;
	
	function parse ($value) {
		
		// Split the date into day, month, year
		list($d, $m, $y) = array_map('intval', explode('/', $value));
		
		// Attempt to fix a two-digit year
		if ($y < 100) {
			$y += ($y < 30) ? 2000 : 1900;
		}
		
		if (checkdate($m, $d, $y)) {
			return date('d/m/Y', mktime(0, 0, 0, $m, $d, $y));
		}
		else {
			throw new FieldException(sprintf('Invalid date "%s" for field "%s"', $value, $this->name));
		}
	}
	
	function from_db ($value) {
		// Split the date into day, month, year
		list($y, $m, $d) = explode('-', $value);
		return $this->parse(sprintf('%d/%d/%d', $d, $m, $y));
	}
	
	function to_db () {
		list($d, $m, $y) = explode('/', $this->value);
		return sprintf('%d-%d-%d', $y, $m, $d);
	}
	
}

class BooleanField extends Field {
	
	public $type = 'checkbox';
	
	function parse ($value) {
		return ($value ? '1' : '0');
	}
	
	function render () {
		
		if ($this->value) {
			$this->attrs['checked'] = 'checked';
		}
		else {
			unset($this->attrs['checked']);
		}
		
		return parent::render();
		
	}
	
}

class SelectField extends Field {
	
	public $choices;
	
	function render () {
		
		// Remove type attr, not used for SELECT fields
		unset($this->attrs['type']);
		
		return parent::render();
		
	}
	
}

/////////////////
/* FILE FIELDS */
/////////////////

class FileField extends Field {
	
	public $type = 'file';
	public $path;
	
	function input ($value) {
		// Check for a valid file upload
		// if (!$value['name']) {
		if (!is_uploaded_file($value['tmp_name'])) {
			// No upload: return original value
			return $this->value;
		}
		
		load('Upload');
		
		try {
			$u = new Upload($value);
			$path = join_path(array(Frix::config('MEDIA_ROOT'), $this->path, $u->filename));
			$u->save($path);
			
			if (method_exists($this, 'process_file')) {
				$this->process_file($path);
			}
			
			return $u->filename;
		}
		catch (Exception $e) {
			return null;
		}
		
	}
	
	function get_path () {
		
		if (!$this->value) {
			return '';
		}
		
		return join_path(array($this->path, $this->value));
	}
	
	function get_real_path () {
		
		if (!$this->value) {
			return '';
		}
		
		return join_path(array(Frix::config('MEDIA_ROOT'), $this->get_path()));
	}
	
	function get_url () {
		
		if (!$this->value) {
			return '';
		}
		
		return url(Frix::config('MEDIA_URL'), $this->get_path());
	}
	
}

class ImageField extends FileField {
	
	// function process_file ($path) {
		// rename($path, '1.txt');
	// }
	
	function get_thumb ($width = null, $height = null) {
		
		load('Image');
		
		$path = $this->get_real_path();
		
		// Value to append to the thumbnail filename
		$extra = '_t';
		
		// Append width and/or height if size changed
		if ($width) {
			$extra .= '_w' . $width;
		}
		if ($height) {
			$extra .= '_h' . $height;
		}
		
		// Split file path and change file name
		$path_info = pathinfo($path);
		$path_info['filename'] .= $extra;
		
		// Join path together
		$new_path = sprintf('%s/%s.%s', $path_info['dirname'], $path_info['filename'], $path_info['extension']);
		
		// If the thumbnail doesn't exists or the original has been modified
		if (!file_exists($new_path) || (filemtime($path) > filemtime($new_path))) {
			// Create image object
			$i = Image::open($path);
			// Adjust size
			$i->fit($width, $height);
			
			$i->save_as($new_path);
		}
		
		return url(Frix::config('MEDIA_URL'), $this->path, sprintf('%s.%s', $path_info['filename'], $path_info['extension']));
		
	}
	
}

////////////////////
/* RELATED FIELDS */
////////////////////

class ForeignKey extends SelectField {
	
	public $index = true;
	public $model_name;
	
	private $meta;
	private $instance;
	
	function input ($value) {
		if ($value === '') {
			return null;
		}
		return parent::input($value);
	}
	
	function get_meta () {
		if (!$this->meta) {
			$this->meta = ModelMeta::meta($this->model_name);
		}
		return $this->meta;
	}
	
	function get_instance () {
		if (!$this->value) {
			return false;
		}
		return $this->get_meta()->one(array($this->get_meta()->pk => $this->value));
	}
	
	function get_choices () {
		$this->choices = array('' => '-');
		$items = $this->get_meta()->all();
		if ($items) {
			foreach ($items as $item) {
				$this->choices[$item->get_pk()] = (string) $item;
			}
		}
		return $this->choices;
	}
	
	function render () {
		$this->get_choices();
		return parent::render();
	}
	
}

class TreeKey extends ForeignKey {
	
	function get_meta () {
		$this->model_name = $this->model->meta()->name;
		return parent::get_meta();
	}
	
	function get_children ($parent_pk = null, $level = 0, $block_id = null) {
		
		static $children;
		
		if ($level === 0) {
			$children = array();
		}
		
		// Get a list of items for the current level (NULL == ROOT)
		$items = $this->get_meta()->all(array($this->name => $parent_pk));
		
		end($items)->is_last = true;
		reset($items)->is_first = true;
		
		// Loop through the list
		foreach ($items as $item) {
			// An item can't be a child of itself, so skip...
			if ($block_id && ($item->get_pk() == $block_id)) {
				continue;
			}
			
			$item->tree_level = $level;
			$children[$item->get_pk()] = $item;
			
			$this->get_children($item->get_pk(), $level + 1);
			
		}
		
		return $children;
		
	}
	
	function get_choices () {
		
		// Empty choice
		$this->choices = array('' => '-');
		
		// Get recursive tree of items (blocking children of the current object)
		$children = $this->get_children(null, 0, $this->model->get_pk());
		
		foreach ($children as $k => $item) {
			$this->choices[$k] = sprintf('%s* %s', str_repeat(' &nbsp; &nbsp;', $item->tree_level), (string) $item);
		}
		
		return $this->choices;
		
	}
	
}

///////////////////
/* BUTTON FIELDS */
///////////////////

class SubmitField extends Field {
	
	public $type = 'submit';

	function __construct ($verbose_name, $options = array()) {
		
		parent::__construct($verbose_name, $options = array());
		
		$this->value = $this->verbose_name;
		
	}
	
}
?>