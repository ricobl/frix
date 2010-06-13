<?
class AuthUser extends Model {
	
	private $authenticated = false;
	
	function setup ($meta) {
		$meta->verbose_name = 'User';
	}
	
	function __toString () {
		return $this->username;
	}
	
	function create_fields () {
		
		$this->add_fields(array(
			// INFO
			'first_name' => new CharField('First Name', array('length' => 30)),
			'last_name' => new CharField('Last Name', array('length' => 40)),
			
			// INTERNAL
			'username' => new CharField('Username', array('length' => 30)),
			'password' => new PasswordField('Password', array('length' => 32, 'editable' => false)),
			'email' => new CharField('E-mail', array('length' => 90)),
			
			// FLAGS
			// Can login on admin
			'is_staff' => new BooleanField('Staff Member', array('default' => false, 'editable' => false)),
			// Can login anywhere else
			'is_active' => new BooleanField('Active', array('default' => false, 'editable' => false)),
			// Is super-user (all permissions granted)
			'is_super' => new BooleanField('Super User', array('default' => false, 'editable' => false)),
		));
		
	}
	
	// Check if the password matches the original
	function check_password ($password) {
		if (!$password) {
			return false;
		}
		return $this->password == md5($password);
	}
	
	// Convert the password to a MD5 hash
	// * this function is automatically called when accessing Model->set_password('xxx')
	function set_password ($value) {
		$this->password = md5($value);
	}
	
	// Authenticate an existent user against a password
	function authenticate ($password) {
		$this->authenticated = $this->check_password($password);
		return $this->authenticated;
	}
	
	// Checks if the user is authenticated
	function is_authenticated () {
		return $this->authenticated;
	}
	
	static public function meta() {
		return ModelMeta::meta(get_class());
	}
}
?>