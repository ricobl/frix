<?
class CmsPage extends Model {
	
	function setup ($meta) {
		$meta->verbose_name = 'Page';
	}
	
	function __toString () {
		return sprintf('%s', $this->title);
	}
	
	function create_fields () {
		$this->add_fields(array(
			'parent' => new TreeKey('Parent Page', array('null' => true)),
			'title' => new CharField('Title', array('length' => 40)),
			'slug' => new CharField('Slug', array('length' => 40)),
			'content' => new TextField('Content'),
			'visible' => new BooleanField('Publish', array('default' => true)),
			'pos' => new IntegerField('Position', array('editable' => false)),
		));
	}
	
	function get_absolute_url () {
		return url(Frix::config('WEB_ROOT'), $this->slug);
	}
	
	static public function meta() {
		return ModelMeta::meta(get_class());
	}
	
}
?>
