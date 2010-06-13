<?
class RelatedField extends Field {

    function __construct ($model_name) {
        $this->model_name = $model_name;
	}
	
    private function set_up () {
        if (!is_a($this->model_name, 'Model')) {
            $this->related_model = ModelMeta::meta($this->model_name);
        }
	}
	
}
	
class ForeignKey extends RelatedField {

    function get () {
		
		parent::set_up();
		
        if (!$this->field) {
			$this->field = sprintf('%s_id', $this->related_model->meta()->table);		
		}
		
        $conditions = array($this->related_model->meta()->pk => $instance->{$this->field});
		
        $query = new Query($this->related_model->meta(), $conditions);
        return $query[0];
		
	}
	
}

class OneToMany extends RelatedField {

    function get () {
		
        parent::set_up();
		
        if (!$this->field) {
			$this->field = sprintf('%s_id', $instance->meta()->table);
		}
		
        $conditions = array($this->field => $instance->{$instance->meta()->pk});
		
        $query = new Query($this->related_model->meta(), $conditions);
        return $query;
		
	}
	
}
?>