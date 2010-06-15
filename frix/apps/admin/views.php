<?
load('Fs');
load('Form');

// TODO: The view could extend from a default view object
class Views {
	
	// Static vars to allow keeping values in multiple instances
	static $root;
	static $route;
	static $context = array();
	
	private $admin;
	
	private $app;
	private $model_slug;
	private $model_class;
	private $options;
	
	private $file_manager_path = 'uploads';
	
	// Re-route the admin command to another view
	function route ($root, $route) {
		
		// Get the root path from where admin is running
		self::$root = url(Frix::config('WEB_ROOT'), $root, '/');
		self::$route = $route;
		
		// Put the root path into the URL
		self::$context['root'] = self::$root;
		
		// TODO: create some type of decorator to require authentication on views
		// to avoid this kind of hack manually checking the route here...
		// Forgot password route?
		if (str_replace('/', '', $route) == 'forgot') {
			return $this->forgot();
		}
		
		// Load Auth app
		$auth_app = Frix::app('auth');
		// Check if user is authorized
		if (!$auth_app->get_user()) {
			return $this->login();
		}
		
		// Load Admin app
		$this->admin = Frix::app('admin');
		
		// List all the installed apps
		foreach (Frix::config('APPS') as $name => $path) {
			
			// Create an App instance
			$app = Frix::app($name);
			
			// Try to import options
			try {
				import(join_path(array($app->path, 'admin.php')));
			}
			// Go to next app on failure
			catch (ImportException $e) {
				continue;
			}
			
			// Any registered model?
			if (array_key_exists($name, $this->admin->registry)) {
				// Inject an URL attribute in the app
				$app->admin_url = url(self::$root, $name);
				self::$context['apps'][$name] = $app;
			}
			
		}
		
		// Initialize breadcrumbs
		self::$context['breadcrumbs'] = array();
		
		// Load route config
		// TODO: make router allow URL 'inclusions' instead of creating multiple routers
		require_once(join_path(array($this->admin->path, 'routes.php')));
		
		// Use the Router to map the command string to a view
		// TODO: try to trick Router to jump the application resolval
		// and use this instance of the views
		$router = new Router($routes, $route);
		$router->start();
		
		// Let the template know what view is running
		self::$context['view_name'] = $router->view_name;
		
	}
	
	////////////////
	/* AUTH VIEWS */
	////////////////
	
	// Login page for allow access
	function login () {
		
		// Load Auth app
		$auth_app = Frix::app('auth');
		
		// Already authorized?
		if ($auth_app->get_user()) {
			// Go to the admin home
			redir(url(self::$root));
		}
		
		// Load AuthUser model
		$auth_app->load_model('AuthUser');
		// Get model meta
		$meta = AuthUser::meta();
		
		$f = new Form;
		$f->add_fields(array(
			'username' => new CharField('Username', array('length' => 30)),
			'password' => new PasswordField('Password', array('length' => 32)),
		));
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			
			$f->input($_POST);
			
			$user = $meta->one(array('username' => $f->get_username()));
			
			if ($auth_app->authenticate($user, $f->get_password())) {
				redir($_SERVER['REQUEST_URI']);
			}
			else {
				self::$context['msg'] = 'Wrong username or password.';
				self::$context['msg_type'] = 'err';
			}
			
		}
		
		self::$context['form'] = $f;
		
		$t = new Template('frix/admin/login');
		echo $t->render(self::$context);
		
	}
	
	// Logout page
	function logout () {
		
		// Load Auth app
		$auth_app = Frix::app('auth');
		
		$auth_app->logout();
		
		redir(url(self::$root));
		
	}
	
	// Forgotten password view
	function forgot () {
		
		// Load Auth app
		$auth_app = Frix::app('auth');
		
		// Already authorized?
		if ($auth_app->get_user()) {
			// Go to the admin home
			redir(url(self::$root));
		}
		
		// Load AuthUser model
		$auth_app->load_model('AuthUser');
		// Get model meta
		$meta = AuthUser::meta();
		
		$f = new Form;
		$f->add_fields(array(
			'email' => new CharField('E-mail', array('length' => 100)),
		));
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			
			// Get form data
			$f->input($_POST);
			
			$email = $f->get_email();
			
			// Check for a valid e-mail address.
			if (!validate_email($email)) {
				self::$context['msg'] = sprintf('Invalid e-mail address "%s"!', $email);
				self::$context['msg_type'] = 'err';
			}
			else {
				// Get user by e-mail address
				$user = $meta->one(array('email' => $email));
				
				if ($user) {
					
					// Create a new password
					$pass = make_pass();
					
					$msg =
						'Your new passord is:' . "\n" .
						$pass . "\n\n" .
						'You can log in using your username:' . "\n" .
						$user->username
					;
					
					// Send the password to the user
					$ok = send_mail('New password', $msg, Frix::app('settings')->get('contact_email'), $user->email);
					
					// Error sending the msg?
					if (!$ok) {
						self::$context['msg'] =
							'Couldn\'t send msg.<br />' .
							'Password not changed.'
						;
						self::$context['msg_type'] = 'err';
					}
					// Message sucessfully sent?
					else {
						// Change user password and save
						$user->set_password($pass);
						$user->save();
						// Redirect with a success msg
						redir('./?sent=1');
					}
					
				}
				else {
					self::$context['msg'] = sprintf('E-mail address "%s" not found!', $email);
					self::$context['msg_type'] = 'err';
				}
			}
			
		}
		else {
			if ($_GET['sent']) {
				self::$context['msg'] =
					'The new password was sent!<br />'.
					'Please check your inbox.'
				;
				self::$context['msg_type'] = 'ok';
			}
			else {
				self::$context['msg'] = 'Type your e-mail to get a new password.';
			}
		}
		
		self::$context['form'] = $f;
		
		$t = new Template('frix/admin/forgot');
		echo $t->render(self::$context);
		
	}
	
	/////////////////////////
	/* FILE BROWSING VIEWS */
	/////////////////////////
	
	function files_list_pop ($root, $path = '') {
		self::$context['popup'] = true;
		return $this->files_list($root, $path);
	}
	
	function files_list ($root, $path = '') {
		
		$fb_root = url(self::$root, $root);
		
		self::$context['filebrowser'] = true;
		
		$this->context_breadcrumbs('Filebrowser', $fb_root);
		
		$files = array();
		
		// Fix empty path
		$path = preg_replace('#(^/)|(/$)#', '', $path);
		
		$real_path = join_path(Frix::config('MEDIA_ROOT'), $this->file_manager_path, $path);
		
		if ($path) {
			
			$current_path = $fb_root;
			$parts = explode('/', $path);
			
			foreach ($parts as $part) {
				$current_path = url($current_path, $part);
				$this->context_breadcrumbs($part, $current_path);
			}
			
			// Remove current dir
			array_pop($parts);
			
			$files[] = array(
				'name' => '.. (' . end($parts) . ')',
				'class' => 'up',
				'size' => '&nbsp;',
				'is_dir' => true,
				'is_empty' => false,
				'link' => url($fb_root, dirname($path)),
			);
		}
		
		if (!is_dir($real_path)) {
			throw new Http404Exception;
		}
		
		if ($_POST['new_dir']) {
			return $this->files_new_dir($real_path);
		}
		elseif ($_POST['new_file']) {
			return $this->files_new($real_path);
		}
		elseif ($_GET['del']) {
			return $this->files_delete($real_path);
		}
		
		if ($_GET['err']) {
			
			self::$context['msg_type'] = 'err';
			
			if ($_GET['err'] == 'bad_upload') {
				load('Upload');
				self::$context['msg'] = Upload::$errors[$_GET['code']];
			}
			else {
				$msg = array(
					'err_upload' => 'Couldn\'t save file, check your permissions.',
					'bad_dir' => 'Invalid or forbidden folder name!',
					'file_exists' => 'File or folder already exists, try a different name.',
					'del_dir' => 'Couldn\'t remove folder, check if it is empty.',
					'del_file' => 'Couldn\'t remove folder, check your permissions.',
					'not_found' => 'Object not found!',
				);
				
				self::$context['msg'] = $msg[$_GET['err']];
			}
			
			
		}
		elseif ($_GET['msg']) {
			$msg = array(
				'new_dir' => 'Folder created sucessfully!',
				'del_dir' => 'Folder removed sucessfully!',
				'del_file' => 'File removed sucessfully!',
				'new_file' => 'File uploaded sucessfully!',
			);
			
			self::$context['msg'] = $msg[$_GET['msg']];
			self::$context['msg_type'] = 'ok';
		}
		
		// Load a list of files not starting with a dot
		$file_list = Fs::dir($real_path, '^[^.].*');
		
		$base_link = url(Frix::config('MEDIA_URL'), $this->file_manager_path, $path);
		
		foreach ($file_list as $file) {
			
			$full_path = join_path($real_path, $file);
			
			$file_obj = array(
				'name' => $file,
			);
			
			if (is_dir($full_path)) {
				$file_obj['size'] = '&nbsp;';
				$file_obj['link'] = url($fb_root, $path, $file);
				$file_obj['is_dir'] = true;
				$file_obj['is_empty'] = Fs::is_empty_dir($full_path);
				$file_obj['class'] = 'dir';
			}
			else {
				$file_obj['size'] = Fs::format_size(Fs::file_size($full_path), 1);
				$file_obj['link'] = url($base_link, $file);
				$file_obj['target'] = '_blank';
				$file_obj['class'] = Fs::extension($file);
			}
			
			$files[] = $file_obj;
			
		}
		
		self::$context['files'] = $files;
		
		$t = new Template('frix/admin/filebrowser/list');
		echo $t->render(self::$context);
		
	}
	
	function files_new_dir ($real_path) {
		
		$status = '';
		$dir_name = $_POST['dir_name'];
		
		if (preg_match('#^[a-z0-9-_/]+$#', $dir_name)) {
			
			$dir_path = join_path($real_path, $dir_name);
			
			if (file_exists($dir_path)) {
				$status = '?err=file_exists';
			}
			elseif (@mkdir($dir_path, 0777, true)) {
				$status = '?msg=new_dir';
			}
			else {
				$status = '?err=new_dir';
			}
			
		}
		else {
			$status = '?err=bad_dir';
		}
		
		redir('./' . $status);
		
	}
	
	function files_new ($real_path) {
		
		$status = '';
		
		load('Upload');
		
		try {
			$up = new Upload($_FILES['file']);
		}
		catch (UploadException $e) {
			$up = false;
			$status = '?err=bad_upload&code=' . $e->getCode();
		}
		
		if ($up) {
			
			$file_path = join_path($real_path, $up->filename);
			
			try {
				
				$up->save($file_path);
				
				$status = '?msg=new_file';
				
			}
			catch (UploadException $e) {
				$status = '?err=err_upload';
			}
			
		}
		
		redir('./' . $status);
		
	}
	
	function files_delete ($real_path) {
		
		$file_name = $_GET['del'];
		
		// Make sure the filename doesn't:
		// - starts with a dot
		// - has any slashes
		if (preg_match('#(^\.)|([\\\\/])#', $file_name)) {
			redir('./?err=not_found');
		}
		elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
			
			$status = '';
			
			$file_path = join_path($real_path, $file_name);
			
			if (is_dir($file_path)) {
				
				$status = '?msg=del_dir';
				
				if (!@rmdir($file_path)) {
					$status = '?err=del_dir';
				}
				
			}
			else {
				
				$status = '?msg=del_file';
				
				if (!@unlink($file_path)) {
					$status = '?err=del_file';
				}
				
			}
			
			redir('./' . $status);
			
		}
		else {
			
			self::$context['file_name'] = $file_name;
			
			// Add confirmation message
			$this->context_breadcrumbs('Delete?');
			
			$t = new Template('frix/admin/filebrowser/delete');
			echo $t->render(self::$context);
			
		}
		
	}
	
	///////////////////////////////////
	/* HELPERS AND CONTEXT FUNCTIONS */
	///////////////////////////////////
	
	// Put together $_FILES and $_POST data
	private function file_and_post_data () {
		
		// Start array using $_POST data
		$new_files = $_POST;
		
		// Look for uploaded files
		foreach ($_FILES as $name => $form_field) {
			
			// Upload props: name, tmp_name, size, error, etc.
			$file_props = array_keys($form_field);
			
			// Use 'name' array to get the number of upload items
			foreach (array_keys($form_field['name']) as $id) {
				
				// Loop through each upload property
				foreach ($file_props as $prop) {
					
					// Loop through upload field
					foreach ($form_field[$prop][$id] as $k => $v) {
						// Assign value, in the format inline_name[id][upload_field_name][upload_property]
						// E.g.: cms_file[1][file][tmp_name]
						$new_files[$name][$id][$k][$prop] = $v;
					}
					
				}
				
			}
			
		}
		
		return $new_files;
		
	}

	// Add a breadcrumb to the context
	private function context_breadcrumbs ($name, $link = '') {
		self::$context['breadcrumbs'][] = $link ? sprintf('<a href="%s">%s</a>', $link, $name) : $name;
	}
	
	// Helper function to load the app and insert it in the context
	private function context_app ($app_name) {
		
		// Load the app
		$this->app = Frix::app($app_name);
		
		// Put the app in the context
		self::$context['app'] = $this->app;
		
		// Add the app to the breadcrumbs
		$this->context_breadcrumbs($this->app->verbose_name, $this->app->admin_url);
		
		return $this->app;
		
	}
	
	// Load model registration options
	private function context_options ($app_name, $model_slug) {
		
		// Get app
		$app = $this->context_app($app_name);
		
		$admin = Frix::app('admin');
		
		// Save model slug
		$this->model_slug = $model_slug;
		// Convert slug to class-name: cms_page -> CmsPage
		$this->model_name = camel($model_slug);
		// Get the Options from the registry
		$this->options = $admin->registry[$app_name][$this->model_name];
		
		// Add to the context
		self::$context['options'] = $this->options;
		self::$context['model'] = $this->options->meta;
		
		// Add 'Items' to the breadcrumbs
		$this->context_breadcrumbs($this->options->meta->verbose_name_plural, url(self::$root, $this->options->meta->admin_url));
		
		return $this->options;
		
	}
	
	// Helper function to load the model, create an instance and insert it in the context
	private function context_instance ($app_name, $model_name, $id) {
		
		// Get the options object
		$options = $this->context_options($app_name, $model_name);
		
		// Load instance
		$instance = $options->load($id);
		
		self::$context['instance'] = $instance;
		
		return $instance;
		
	}
	
	///////////
	/* VIEWS */
	///////////
	
	function render_content ($content, $templates) {
	}
	
	// Show the list of installed apps
	function index () {
		$t = new Template('frix/admin/index');
		echo $t->render(self::$context);
	}
	
	// Show a list of models from an app
	function app ($app_name) {
		
		// Get the app
		$this->context_app($app_name);
		
		$registry = Frix::app('admin')->registry[$app_name];
		
		// If there's only one model, redirect to the model page
		if (count($registry) == 1) {
			$first = reset($registry);
			redir(self::$root . $first->meta->admin_url);
		}
		
		// Get the meta models
		// $models = array();
		// foreach ($registry as $k => $options) {
			// $models[$k] = $options->meta;
		// }
			
		// Save model-list into the context
		self::$context['models_options'] = $registry;
		
		$t = new Template(
			sprintf('frix/admin/%s/app', $app_name),
			'frix/admin/app'
		);
		echo $t->render(self::$context);
		
	}
	
	// Show a list of objects from a model
	function items ($app_name, $model_slug) {
		
		// Get the Model options object
		$options = $this->context_options($app_name, $model_slug);
		
		// Pass post data to the options class to list the object items
		$content = $options->items_view(self::$context);
		
		// The custom view returned content?
		// TODO: apply this on other views to create a standard behaviour
		if ($content) {
			// Show the returned content
			echo $content;
		}
		// No content?
		else {
			// Render the default template for this view
			$t = new Template(
				sprintf('frix/admin/%s/items', $app_name),
				sprintf('frix/admin/%s/%s/items', $app_name, $model_slug),
				'frix/admin/items'
			);
			echo $t->render(self::$context);
		}
		
	}
	
	// Create a new object
	function add ($app_name, $model_slug) {
		
		// Load model options
		$options = $this->context_options($app_name, $model_slug);
		
		// Check if model accepts adding (avoid leaving this to the developer)
		if (!$options->can_add) {
			throw new Http404Exception();
		}
		
		// Add 'New Item' to the breadcrumbs
		$this->context_breadcrumbs(sprintf('Create %s', $options->meta->verbose_name));
		
		// Pass post data to the options class to add the object
		$options->add_view($this->file_and_post_data(), self::$context);
		
		$t = new Template(
			sprintf('frix/admin/%s/add', $app_name),
			sprintf('frix/admin/%s/%s/add', $app_name, $model_slug),
			'frix/admin/add'
		);
		echo $t->render(self::$context);
		
	}

	// Change an existent object
	function change ($app_name, $model_slug, $id) {
		// Get the instance
		$instance = $this->context_instance($app_name, $model_slug, $id);
		$options = self::$context['options'];
		
		// Check if model accepts changing (avoid leaving this to the developer)
		if (!$options->can_change) {
			throw new Http404Exception();
		}
		
		// Add 'Changing Item X' to the breadcrumbs
		$this->context_breadcrumbs(sprintf('Changing %s "%s"', $instance->meta()->verbose_name, $instance));
		
		// Pass the instance and post data to the options class to change the object
		$options->change_view($instance, $this->file_and_post_data(), self::$context);
		
		$t = new Template(
			sprintf('frix/admin/%s/change', $app_name),
			sprintf('frix/admin/%s/%s/change', $app_name, $model_slug),
			'frix/admin/change'
		);
		echo $t->render(self::$context);
		
	}
	
	// Move an item up or down
	function order ($app_name, $model_slug, $id, $direction) {
		// Get the instance
		$instance = $this->context_instance($app_name, $model_slug, $id);
		$options = self::$context['options'];
		
		// Check if model accepts changing (avoid leaving this to the developer)
		if (!$options->can_change) {
			throw new Http404Exception();
		}
		
		// Pass the instance and direction to the options class to re-order the object
		$options->order_view($instance, $direction, self::$context);
		
	}
	
	// Deletes an existent object
	function delete ($app_name, $model_slug, $id) {
		
		// Get the instance
		$instance = $this->context_instance($app_name, $model_slug, $id);
		$options = self::$context['options'];
		
		// Check if model accepts deleting (avoid leaving this to the developer)
		if (!$options->can_delete) {
			throw new Http404Exception();
		}
		
		// Add 'Item X' to the breadcrumbs
		$this->context_breadcrumbs(sprintf('%s "%s"', $instance->meta()->verbose_name, $instance), '../');
		// Add confirmation message
		$this->context_breadcrumbs('Delete?');
		
		// Pass the instance and post data to the options class to delete the object
		$options->delete_view($instance, $context);
		
		$t = new Template(
			sprintf('frix/admin/%s/delete', $app_name),
			sprintf('frix/admin/%s/%s/delete', $app_name, $model_slug),
			'frix/admin/delete'
		);
		echo $t->render(self::$context);
		
	}
	
}
?>
