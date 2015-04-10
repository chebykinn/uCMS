<?php
class Settings{
	private $list;
	public function add($name, $value){
		$query = new Query('{settings}');
		$query->insert(array('name' => $name, 'value' => $value))->execute();
	}

	public function load(){
		$query = new Query('{settings}');
		$this->list = $query->select(array('name', 'value'))->execute();
	} 

	public function get($name){
		foreach ($this->list as $setting) {
			if($setting['name'] === $name) return $setting['value'];
		}
		return "";
	}

	public function set($name, $value){
	
	}

	public function remove($name, $value){

	}


}
?>