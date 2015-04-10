<?php
class Management{
	private $data;
	private $perpage;
	private $order;
	private $sort;

	public function __construct($data, $owner){
		$this->owner = $owner;
		$this->data = $data;
	}

	public function add($p){

	}

	public function update($p){

	}

	public function delete($p){

	}

	public function printTable(){
		varDump($this->data);
	}
}
?>