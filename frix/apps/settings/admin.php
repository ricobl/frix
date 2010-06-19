<?
// Get models
Frix::model('settings', 'SettingsProperty');

class SettingsPropertyOptions extends AdminOptions {
	
	public $list_display = array('name', 'value');
	
	public $can_add = false;
	public $can_delete = false;
	public $can_change = true;
	
}

// Register
Frix::app('admin')->register('SettingsProperty', 'SettingsPropertyOptions');
?>
