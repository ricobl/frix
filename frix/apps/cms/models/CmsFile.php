<?
class CmsFile extends Model {
	
	function setup ($meta) {
		$meta->verbose_name = 'File';
	}
	
	function __toString () {
		return sprintf('%s', $this->name);
	}
	
	function create_fields () {
		
		// Load CmsPage related model (fix this...)
		// TODO: find out a way to get the app module through the model
		$app = Frix::model('cms', 'CmsPage');
		
		$this->add_fields(array(
			'parent' => new ForeignKey('Parent Page', array('model_name' => 'CmsPage', 'null' => True)),
			'name' => new CharField('Name', array('length' => 80)),
			'file' => new FileField('File', array('length' => 100, 'path' => 'cms/file')),
			// 'visible' => new BooleanField('Visible'),
		));
		
	}
	
	static public function meta() {
		return ModelMeta::meta(get_class());
	}
}
?>
