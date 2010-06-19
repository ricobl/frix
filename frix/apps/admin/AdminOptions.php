<?
class BaseAdminOptions {
	
	// ModelMeta instance
	public $meta;
	// Model instance
	public $instance;
	
	// Data to feed the form
	public $input_data = array();
	
	// Columns to display on 'items' view
	public $list_display;
	public $head_display;
	
	// Permissions
	public $can_add = true;
	public $can_change = true;
	public $can_delete = true;
	
	function __construct ($obj) {
		
		// Figure out what kind of object we're using
		// ModelMeta: use it
		if (is_a($obj, 'ModelMeta')) {
			$this->meta = $obj;
		}
		// Model: use it's meta and keep the instance
		elseif (is_a($obj, 'Model')) {
			$this->meta = $obj->meta();
			$this->instance = $obj;
		}
		// Other (string?): get the ModelMeta based on Model name
		else {
			$this->meta = ModelMeta::meta($obj);
		}
		
	}
	
	function get_name () {
		return $this->meta->name;
	}
	
}

class AdminOptions extends BaseAdminOptions {
	
	// Field to apply custom ordering
	public $custom_position;
	
	private $form;
	public $inlines;
	private $_inlines;
	
	///////////////////////////
	/* ITEMS LISTING RELATED */
	///////////////////////////
	
	// Shows the value for a column specified in 'list_display' property
	// It may be a method in the Options class, in the Model, or a field-name
	function list_display_header ($name) {
		
		$field = $this->meta->model->fields[$name];
		
		// Look for a method in this class
		if (method_exists($this, $name)) {
			return $this->$name();
		}
		// If field exists, use field name
		elseif ($field) {
			return $field->verbose_name;
		}
		// Otherwise, use the name itself
		else {
			return $name;
		}
	}
	
	// Shows the value for a column specified in 'list_display' property
	// It may be a method in the Options class, in the Model, or a field-name
	function list_display_column ($instance, $name) {
		// Look for a method in this class
		if (method_exists($this, $name)) {
			return $this->$name($instance);
		}
		// Or a method in the instance
		elseif (method_exists($instance, $name)) {
			return $instance->$name();
		}
		// Is a boolean field?
		elseif (is_a($instance->fields[$name], 'BooleanField')) {
			// return sprintf('<a href="%d/toggle/%s/">%s</a>', $instance->get_pk(), $name, $instance->$name ? 'yes' : 'no');
			return $instance->$name ? 'yes' : 'no';
		}
		// Otherwise, use field value
		else {
			return truncate(strip_tags($instance->$name));
		}
	}
	
	// Search for a TreeKey field and return if found
	function get_tree_field () {
		foreach ($this->meta->model->fields as $name => $field) {
			if (is_a($field, 'TreeKey')) {
				return $field;
			}
		}
		return null;
	}
	
	/////////////////////
	/* CUSTOM ORDERING */
	/////////////////////
	
	function order_column ($instance) {
		
		// Order button format: <a href="$pk/order/$direction" class="bt_$direction">$direction</a>
		$bt_format = sprintf('<a href="%1$d/order/%%1$s/" class="bt_%%1$s" title="Move %%1$s">%%1$s</a>', $instance->get_pk());
		
		$bts = array();
		
		if (!property_exists($instance, 'is_first')) {
			$bts[] = sprintf($bt_format, 'up');
		}
		
		if (!property_exists($instance, 'is_last')) {
			$bts[] = sprintf($bt_format, 'down');
		}
		
		return implode(' ', $bts);
	}
	
	//////////////////////////
	/* FORMS (ADD / CHANGE) */
	//////////////////////////
	
	function load ($id) {
		$this->instance = $this->meta->one(array('id' => $id));
		return $this->instance;
	}
	
	function get_form () {
		
		if (!$this->form) {
			// Create a form based on a Model instance or a ModelMeta object
			// This will create an empty form for adding or a filled form for editing
			$this->form = new ModelForm($this->instance ? $this->instance : $this->meta->model);
			
			// If there are input data
			if ($this->input_data) {
				// Feed the form with the new data
				$this->form->input($this->input_data);
			}
		}
		
		return $this->form;
		
	}
	
	function get_inlines () {
		
		if (!$this->inlines) {
			return array();
		}
		
		if ($this->_inlines) {
			return $this->_inlines;
		}
		
		$this->_inlines = array();
		
		foreach ($this->inlines as $key_name) {
			$this->_inlines[$key_name] = new AdminInlineOptions($key_name, $this);
		}
		
		return $this->_inlines;
		
	}
	
	function get_inline_forms () {
		
		$inlines = $this->get_inlines();
		
		$forms = array();
		
		// Create inline forms for each inline model
		foreach ($inlines as $key_name => $inline) {
			
			// Each inline form may be a group rows
			$forms[] = $inline->get_forms();
			
		}
		
		return $forms;
		
	}
	
	///////////////////////
	/* DATA MANIPULATION */
	///////////////////////
	
	function save () {
		
		if (!$this->input_data) {
			return false;
		}
		
		$f = $this->get_form();
		$f->save();
		
		$fk = $f->model->get_pk();
		
		// There are any inline items?
		if ($this->inlines) {
			
			// Save each inline model
			foreach ($this->get_inlines() as $key_name => $inline) {
				
				// TODO: check if $model_name is an Inline or a Model
					// If it's an Inline, instantiate it passing the model-name
					// If it's a Model, instantiate the default 'AdminInlineOptions' passing the model-name
				
				// Pass input data to inline model
				// $inline->input_data = $this->input_data[uncamel($key_name)];
				
				// Set FK id
				$inline->get_fk()->value = $fk;
				
				$inline->save();
				
			}
			
		}
		
		return $f->model;
		
	}
	
	///////////
	/* VIEWS */
	///////////
	
	// Show a list of objects from a model
	function items_view (&$context) {
		
		// No 'list_display' set, use all model fields
		if (!$this->list_display) {
			$this->list_display = array_keys($this->meta->model->fields);
		}
		
		// No 'head_display' set, use list_display
		if (!$this->head_display) {
			$this->head_display = $this->list_display;
		}
		
		// Different number of columns and headers?
		if (count($this->list_display) != count($this->head_display)) {
			throw new Exception('The number of columns must be the same on "list_display" and "head_display"');
		}
		
		if ($this->custom_position) {
			// Look for the ordering field on the columns to display
			// and replace with custom display methods
			// or add the methods to the end if not found
			if (!$k = array_search($this->custom_position, $this->list_display, true)) {
				$k = count($this->list_display);
			}
			
			$this->list_display[$k] = 'order_column';
			$this->head_display[$k] = 'Up / Down';
			
			// Setup the model ordering
			$this->meta->ordering = array($this->custom_position, 'ASC');
		}
		
		// Deal with messages
		if ($_GET['msg'] == 'added') {
			$context['msg'] = sprintf('%s added successfully.', $this->meta->verbose_name);
			$context['msg_type'] = 'ok';
		}
		elseif ($_GET['msg'] == 'changed') {
			$context['msg'] = sprintf('%s changed successfully.', $this->meta->verbose_name);
			$context['msg_type'] = 'ok';
		}
		elseif ($_GET['msg'] == 'deleted') {
			$context['msg'] = sprintf('%s deleted successfully.', $this->meta->verbose_name);
			$context['msg_type'] = 'ok';
		}

		// Get the TreeField for this model (or null if not found)
		$tree_field = $this->get_tree_field();
		
		if ($tree_field) {
			
			// Get the object tree
			$context['items'] = $tree_field->get_children();
			
			// Get application name and model slug to look for custom templates
			$app_name = $this->meta->app->name;
			$model_slug = uncamel($this->meta->name);
			
			// Render a custom template for tree-models
			$t = new Template(
				sprintf('frix/admin/%s/items_tree', $app_name),
				sprintf('frix/admin/%s/%s/items_tree', $app_name, $model_slug),
				'frix/admin/items_tree'
			);
			return $t->render($context);
			
		}
		else {
			// Get the object list
			$context['items'] = $this->meta->all();
			
			// Mark first and last items
			end($context['items'])->is_last = true;
			reset($context['items'])->is_first = true;
		}
		
		
	}
	
	// Create an object
	function add_view ($data, &$context) {
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			
			$this->input_data = $data;
			$instance = $this->save();
			
			$pos_field = $this->custom_position;
			
			if ($pos_field && !$instance->$pos_field) {
				$instance->$pos_field = $instance->get_pk();
				$instance->save();
			}
			
			redir('../?msg=added');
			
		}
		
		$context['form'] = $this->get_form();
		$context['inlines'] = $this->get_inlines();
		
	}
	
	// Change an existent object
	function change_view ($instance, $data, &$context) {
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			
			$this->input_data = $data;
			$this->save();
			
			redir('../?msg=changed');
			
		}
		
		$context['form'] = $this->get_form();
		$context['inlines'] = $this->get_inlines();
		
	}
	
	// Deletes an existent object
		// GET: confirmation
		// POST: deletion and redirection
	function delete_view ($instance, &$context) {
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			
			if ($_POST['delete']) {
				// Deletes the instance
				$instance->delete();
				
				// Return to the item listing page for this model  (with 'deleted' message)
				redir('../../?msg=deleted');
			}
			else {
				// Return to the item listing page (without 'deleted' message)
				redir('../../');
			}
			
			
		}
		
		// Create the form
		$f = new Form();
		$f->add_fields(array(
			'yes' => new SubmitField('Yes'),
			'no' => new SubmitField('No'),
		));
		$context['form'] = $f;
		
	}
	
	function order_view ($instance, $direction, &$context) {
		
		// Current ordering value
		$pos_name = $this->custom_position;
		$pos = $instance->$pos_name;
		
		// Setup query params
		$params = array($pos);
		
		$tree_field = $this->get_tree_field();
		$tree_field_filter = '';
		if ($tree_field) {
			$v = $instance->{$tree_field->name};
			$tree_field_filter = sprintf('AND %s %s %%s', escape($tree_field->name), is_null($v) ? 'IS' : '=');
			$params[] = $v;
		}
		
		// Create a raw query to get the item which will be swapped by this instance
		// Moving up:
			// look for a directly lower value, use descending order to get the closest value
		// Moving down:
			// look for a directly greater value, use ascending order to get the closest value
			// SELECT * FROM gm_cms_page WHERE pos >|< %d ORDER BY pos DESC|ASC LIMIT 1
		$sql = sprintf('SELECT * FROM %1$s WHERE %2$s %4$s %%d %5$s ORDER BY %2$s %3$s LIMIT 1',
			$this->meta->table_safe,
			escape($pos_name),
			($direction == 'up') ? 'DESC' : 'ASC',
			($direction == 'up') ? '<' : '>',
			$tree_field_filter
		);
		
		// Create a query instance
		$q = new Query($this->meta);
		// Execute and fetch the raw query
		$data = $q->sql($sql, $params);
		
		// Check for data
		if ($data) {
			
			// Data is an array of one row
			// Create an instance based on the first row
			$next = $q->create($data[0], false);
			
			// Swap position values
			$instance->$pos_name = $next->$pos_name;
			$next->$pos_name = $pos;
			
			// Save
			$instance->save();
			$next->save();
			
		}
		
		if ($_SERVER['HTTP_REFERER']) {
			redir($_SERVER['HTTP_REFERER']);
		}
		else {
			redir('../../../');
		}
	}
	
}

class AdminInlineOptions extends BaseAdminOptions {
	
	var $extra = 2;
	
	// Parent AdminOptions
	var $parent;
	var $fk;
	
	var $model_slug;
	var $field_name_format;
	
	private $_forms;
	
	function __construct ($obj, $parent) {
		
		parent::__construct($obj);
		$this->parent = $parent;
		
		// Block FK from editing
		$this->get_fk()->editable = false;
		
		// Get model-slug from ModelMeta
		$this->model_slug = uncamel($this->meta->name);
		
		// Set field-name format: inline_model_name[sequence_or_id_number][field_name]
		// $this->field_name_format = $this->model_slug . '[%d][%%s]';
		$this->field_name_format = $this->model_slug . '[%s][%%s]';
		
	}
	
	///////////////
	/* RELATIONS */
	///////////////
	
	// Get the FK name from the inline model
	function get_fk () {
		
		if (!$this->fk) {
			$this->fk = $this->search_fk($this->meta->model->fields);
		}
		
		return $this->fk;
		
	}
	
	// Search for the foreign-key field related to the parent model
	function search_fk ($fields) {
		foreach ($fields as $name => $field) {
			// Is a FK and it's model-name matches the parent model-name
			if (is_a($field, 'ForeignKey') && ($field->model_name == get_class($this->parent->meta->model))) {
				// Get FK name
				return $field;
			}
		}
	}
	
	//////////////////////////
	/* FORMS (ADD / CHANGE) */
	//////////////////////////
	
	function set_field_name_format ($id) {
		return sprintf($this->field_name_format, $id);
	}
	
	function get_forms () {
		
		if ($this->_forms) {
			return $this->_forms;
		}
		
		// Fk name
		$fk = $this->fk->name;
		
		// Fk value
		$fk_id = null;
		
		$models = array();
		
		// Parent instance?
		if ($this->parent->instance) {
			
			// Fk value
			$fk_id = $this->parent->instance->get_pk();
			
			// Load child models by the FK name using parent ID
			$rows = $this->meta->all(array($fk => $fk_id));
			
			// Populate list indexing by PK
			foreach ($rows as $row) {
				$models[$row->get_pk()] = $row;
			}
			
		}
		
		// Create empty models for extra rows
		for ($i = 0; $i < $this->extra; $i++) {
			$models[] = new $this->meta->name;
		}
		
		if ($this->parent->input_data) {
			$this->input_data = $this->parent->input_data[$this->model_slug];
		}
		
		$this->_forms = array();
		
		// Create a form for each Model Instance
		foreach ($models as $id => $model) {
			
			$f = new InlineModelForm($model);
			
			// Only delete inlines if editing
			if ($this->parent->instance) {
				$f->add_field('delete', new BooleanField('Delete'));
			}
			
			// Change identifier if there's no model ID
			// To avoid conflicts between new rows and existent ones
			if (!$model->get_pk()) {
				$id = 'new_' . $id;
			}
			
			// Set field name format
			$f->field_name_format = $this->set_field_name_format($id);
			
			// Get FK field
			$form_fk = $this->search_fk($f->fields);
			// Block from editing
			$form_fk->editable = false;
			// Set FK id
			$form_fk->value = $fk_id;
			
			if ($this->input_data) {
				$f->input($this->input_data[$id]);
			}
			
			$this->_forms[$id] = $f;
			
		}
		
		return $this->_forms;
		
	}
	
	// Get the fields from the first form
	function get_fields () {
		$form = reset($this->get_forms());
		return $form->fields;
	}
	
	///////////////////////
	/* DATA MANIPULATION */
	///////////////////////
	
	function save () {
		
		$forms = $this->get_forms();
		
		foreach ($forms as $i => $f) {
			
			// Feed form
			$f->input($this->input_data[$i]);
			
			// Get the 'delete' field value and remove the injected field
			$delete = $f->fields['delete']->value;
			unset($f->fields['delete']);
			
			// Check if we're editing or adding a model
			if (!$f->model->get_pk() && !$delete) {
				
				$empty = true;
				
				// Look for non-empty fields
				foreach ($f->fields as $name => $field) {
					// If field is not the FK, it's editable and not empty, we can save this form
					if (($field->name != $this->get_fk()->name) && $field->editable && $field->value) {
						// Set the flag and exit the loop
						$empty = false;
						break;
					}
				}
				
				// If no field values found, continue with the next form
				if ($empty) {
					continue;
				}
				
			}
			else {
				if ($delete) {
					$f->model->delete();
					continue;
				}
			}
			
			// Set FK id
			$fk_name = $this->get_fk()->name;
			$f->fields[$fk_name]->value = $this->get_fk()->value;
			
			// Save
			$f->save();
			
			// Set field name format
			$f->field_name_format = $this->set_field_name_format($f->model->get_pk());
			
		}
		
	}
	
}
?>
