<?
// Get models
Frix::model('auth', 'AuthUser');

class AuthUserOptions extends AdminOptions {
	
	public $list_display = array('username', 'email', 'first_name', 'last_name');
	
	public $can_add = false;
	public $can_delete = false;
	
	// Change an existent object
	function change_view ($instance, $data, &$context) {
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			
			$this->input_data = $data;
			
			// Trying to change password?
			if ($data['password']) {
				
				// Checks if passwords match
				if ($data['password'] == $data['password_conf']) {
					// Use AuthUser::set_password to change the password
					$instance->set_password($data['password']);
				}
				else {
					$context['msg'] = 'Password confirmation does not match.';
					$context['msg_type'] = 'error';
				}
				
			}
			
			// No error?
			if (!$context['msg']) {
				// Save and return to the listing page
				$this->save();
				redir('../?msg=changed');
			}
			
		}
		
		// Get form and password field
		$f = $this->get_form();
		$password = $f->fields['password'];
		
		// Enable and clear password field
		$password->editable = true;
		$password->value = '';
		
		// Move password field to the end
		unset($f->fields['password']);
		$f->fields['password'] = $password;
		
		// Add password confirmation field
		$f->add_field('password_conf', new PasswordField('Confirm'));
		
		$context['form'] = $f;
		$context['inlines'] = $this->get_inlines();
		
	}
	
}

// Register
Frix::app('admin')->register('AuthUser', 'AuthUserOptions');
?>
