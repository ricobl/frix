<?
class CmsMenu extends Model {
	
	function setup ($meta) {
		$meta->verbose_name = 'Menu';
	}
	
	function __toString () {
		return sprintf('%s', $this->title);
	}
	
	function create_fields () {
		$this->add_fields(array(
			'title' => new CharField('Title', array('length' => 40)),
			'slug' => new CharField('Slug', array('length' => 40)),
			'visible' => new BooleanField('Publish', array('default' => True)),
		));
	}
	
	static public function meta() {
		return ModelMeta::meta(get_class());
	}
	
}
?>