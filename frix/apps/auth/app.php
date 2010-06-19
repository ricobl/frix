<?
class AuthApp extends App {
	
	const SESSION_COOKIE_KEY = 'frix_auth_user';
	
	public $user;
	
	public $verbose_name = 'Accounts';
	
	function __construct ($name) {
		parent::__construct($name);
		
		// Start session
		session_start();
	}
	
	function authenticate ($user, $password) {
		
		if ($user) {
			
			if ($user->check_password($password)) {
				return $this->set_user($user);
			}
			
		}
		
		return false;
		
	}
	
	function set_user ($user) {
		
		if (is_a($user, 'AuthUser')) {
			$this->user = $user;
		}
		else {
			$this->user = $this->load_user($user);
		}
		
		if ($this->user) {
			$_SESSION[AuthApp::SESSION_COOKIE_KEY] = $this->user->get_pk();
		}
		
		return $this->user;
		
	}
	
	function logout () {
		unset($this->user);
		unset($_SESSION[AuthApp::SESSION_COOKIE_KEY]);
	}
	
	function load_user ($id) {
		
		$id = intval($id);
		$user = null;
		
		if ($id) {
			
			// Load AuthUser model
			$this->load_model('AuthUser');
			
			// Get user
			$user = AuthUser::meta()->filter(array('is_active' => True))->filter(array('is_staff' => True))->one(array('id' => $id));
			
		}
		
		return $user;
		
	}
	
	function get_user () {
		
		if (!$this->user) {
			$this->user = $this->load_user($_SESSION[AuthApp::SESSION_COOKIE_KEY]);
		}
		
		return $this->user;
		
	}
	
}
?>
