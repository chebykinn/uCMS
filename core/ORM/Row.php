<?php
namespace uCMS\Core\ORM;

class Row{
	private $model;
	private $data;

	public function __construct($model, $data){
		$this->model = $model;
		$this->data = $data;

		foreach ($this->data as $key => $value) {
			$this->$key = $value;
		}
	}

	final public function addData($key, $value){
		$this->data->$key = $value;
		$this->$key = $value;
	}

	final public function getData(){
		return $this->data;
	}

	public function __get($name){
		if( property_exists($this->data, $name) ){
			return $this->data->$name;
		}
		$association = $this->model->GetBinding($name);
		// \uCMS\Core\Debug::PrintVar($name);
		// \uCMS\Core\Debug::PrintVar($association);
		if( !empty($association) && class_exists($association['name']) ){
			$value = "";
			$class = new $association['name']();
			$assocKey = $class->primaryKey();
			if(isset($association['options']['key']) ){
				$assocKey = $association['options']['key'];
			}
			if( isset($this->$assocKey) ){
				switch ($association['type']) {
					case 'hasMany':
						$conditions = !empty($association['options']['conditions']) ? $association['options']['conditions'] : array();
						$conditions[$assocKey] = $this->$assocKey;
						$value = $class->find($conditions);
					break;
					
					case 'belongsTo':
							$value = $class->find($this->$assocKey);
					break;
				}
				$this->addData($name, $value);
				return $value;
			}
		}

	}

	// public function __set($name, $value){
	// 	\uCMS\Core\Debug::PrintVar($name);
	// 	$this->model->modified = true;
	// }

	public function __call($name, $arguments){
		if (method_exists($this->model, $name)){
			return call_user_func_array(array($this->model, $name), array_merge(array($this), $arguments));
		}
	}
}

?>