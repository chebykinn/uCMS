<?php
namespace uCMS\Core\ORM;

class Row{
	private $model;
	private $data;

	public function __construct($model, $data){
		$this->model = $model;
		$this->data = $data;
	}

	final public function addData($key, $value){
		$this->data->$key = $value;
	}

	final public function getData(){
		return $this->data;
	}

	final public function getColumns(){
		$columns = get_object_vars($this->data);
		return $columns;
	}

	public function __get($name){
		if( property_exists($this->data, $name) ){
			return $this->data->$name;
		}
		$association = $this->model->GetBinding($name);
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

	public function __isset($name){
		return property_exists($this->data, $name);
	}

	public function __call($name, $arguments){
		$objectMethods = get_class_methods('uCMS\\Core\\Object');
		if (method_exists($this->model, $name)){
			if( !in_array($name, $objectMethods) ){
				return call_user_func_array([$this->model, $name], array_merge([$this], $arguments));
			}else{
				return call_user_func_array([$this->model, $name], $arguments);
			}
		}
	}

	public function __set($name, $value){
		$setCallback = "set$name";
		if( method_exists($this->model, $setCallback) ){
			$value = call_user_func_array(array($this->model, $setCallback), [$value]);
		}

		// Save old key to be able to locate row in database
		if( $name === $this->model->primaryKey() && isset($this->$name) ){
			$this->_oldkey = $this->$name;
		}

		if( $this->$name !== $value ){
			$this->addData($name, $value);
			$this->model->setModified();
		}
	}
}

?>