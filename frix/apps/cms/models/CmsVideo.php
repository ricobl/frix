<?
class CmsVideo extends Model {
	
	function setup ($meta) {
		$meta->verbose_name = 'Video';
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
			'file' => new FileField('Video File', array('length' => 100, 'path' => 'cms/video/file')),
			'image' => new ImageField('Image File', array('length' => 100, 'path' => 'cms/video/image')),
		));
		
	}
	
	static public function meta() {
		return ModelMeta::meta(get_class());
	}
}
?>
