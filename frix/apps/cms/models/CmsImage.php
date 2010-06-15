<?
Frix::model('cms', 'CmsFile');

class CmsImage extends CmsFile {
	
	function setup ($meta) {
		$meta->verbose_name = 'Image';
	}
	
	function create_fields () {
		parent::create_fields();
		$this->add_field(
			'file', new ImageField('File', array('length' => 100, 'path' => 'cms/image'))
		);
	}
	
	static public function meta() {
		return ModelMeta::meta(get_class());
	}
}
?>
