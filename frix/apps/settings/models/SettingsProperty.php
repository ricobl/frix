<?
class SettingsProperty extends Model {
	
	function setup ($meta) {
		$meta->verbose_name = 'Property';
		$meta->verbose_name_plural = 'Properties';
	}
	
	function __toString () {
		return $this->name;
	}
	
	function create_fields () {
		
		$this->add_fields(array(
			'name' => new CharField('Name', array('length' => 30, 'editable' => false)),
			'value' => new CharField('Value', array('length' => 255)),
		));
		
	}
	
	static public function meta() {
		return ModelMeta::meta(get_class());
	}
}
?>
