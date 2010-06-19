<?
class Form {
	
	public $fields;
	
	// HTML attributes
	public $attrs = array();
	
	// Field display format
	public $field_format = '<p>%s</p>';
	
	// Set field-name format
	public $field_name_format = '%s';
	
	// Extra content to show inside the form
	public $extra;
	
	function __construct () {
		// Set default form action and method if they are empty
		def($this->attrs['action'], './');
		def($this->attrs['method'], 'post');
	}
	
	public function __call ($command, $params) {
		
		// Break the command into a method prefix and a field name
		list($method, $field) = explode('_', $command, 2);
		
		// Check if the field exists
		if (array_key_exists($field, $this->fields)) {
			// Set field value
			if ($method == 'set') {
				return $this->set($field, $params[0]);
			}
			// Or get field value
			elseif ($method == 'get') {
				return $this->get($field);
			}
		}
		
	}
	
	final function set ($field, $value) {
		$this->fields[$field]->value = $value;
		// Enable chain
		return $this;
	}
	final function get ($field) {
		return $this->fields[$field]->value;
	}
	
	function add_field ($name, &$field) {
		
		if (!$name || !$field) {
			return;
		}
		
		// Let the field know its parent
		$field->form = $this;
		
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
	
	function input ($data) {
		// Fill field values
		foreach ($this->fields as $name => $field) {
			// Set field value if it's editable
			if (($field->editable) && isset($data[$field->name])) {
				$field->value = $field->input($data[$field->name]);
			}
		}
	}
	
	function render () {
		
		// Create the template using the class-tree to find a template file
		$t = new Template(get_parent_classes($this, 'frix/form/%s'));
		
		// Set default form action and method if they are empty
		def($this->attrs['action'], './');
		def($this->attrs['method'], 'post');
		
		// Fill the context values
		$context = array(
			'form' => $this,
		);
		
		// Return the rendered template
		return $t->render($context);
		
	}
	
	function render_field ($field) {
		
		$label = new Label($field);
		
		return sprintf($this->field_format, $label->render());
		
	}
	
	// Render the form and fields
	function render_fields () {
		
		// Start buffer
		$buffer = '';
		
		// Loop through fields and render them all
		foreach ($this->fields as $field) {
			
			// TODO: use sprintf to customize the field format
			// $buffer .= $field->render() . '<br />' . "\n";
			
			// If field is editable
			if ($field->editable) {
				// Render a the form-row for this field
				$buffer .= $this->render_field($field);
			}
			
		}
		
		return $buffer;
		
	}
	
}

class ModelForm extends Form {
	
	public $model;
	
	function __construct ($model) {
		
		if (is_a($model, 'Model')) {
			$this->model = $model;
		}
		else {
			$this->model = new $model;
		}
		
		$this->fields =& $this->model->fields;
		
		foreach ($this->fields as $field) {
			$field->form = $this;
		}
		
		parent::__construct();
		
	}
	
	function save () {
		return $this->model->save();
	}
	
}

class InlineModelForm extends ModelForm {
	
	public $field_format = '<td>%s</td>';
	
	public $field_name_format = '%s';
	
	function render_field ($field) {
		$field->attrs['name'] = sprintf($this->field_name_format, $field->name);
		return sprintf($this->field_format, $field->render());
	}
	
	function render () {
		
		// Extra data will be used to render multiple lines
		// If not set, create a single line
		if (!$this->extra) {
			$this->extra = sprintf('<tr>%s</tr>', $this->render_fields());
		}
		
		return parent::render();
		
	}
	
}

/* OTHER FORM HELPERS */

class Label {
	
	var $field;
	var $contents;
	// HTML attributes
	var $attrs = array();
	
	function __construct ($field) {
		$this->field = $field;
		$this->contents = $field->render();
		$this->attrs['for'] = $field->attrs['id'];
		
		// If field have an 'id', link with the 'for' label attribute
		if ($this->field->attrs['id']) {
			$this->attrs['for'] = $field->attrs['id'];
		}
	}
	
	function render () {
		
		// Create the template using the class-tree to find a template file
		$t = new Template(get_parent_classes($this, 'frix/form/%s'));
		
		// Fill the context values
		$context = array(
			'label' => $this,
			'contents' => $this->contents,
		);
		
		// Return the rendered template
		return $t->render($context);
		
	}
	
}
?>
