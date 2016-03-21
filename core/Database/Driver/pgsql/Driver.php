<?php
/**
* This file contains implementation of driver interface for PostreSQL.
*
* @author Ivan Chebykin
* @author ivan4b69@gmail.com
* @since 2.0
*/
namespace uCMS\Core\Database\Driver\pgsql;

use uCMS\Core\Database\Driver\DriverInterface;
use \uCMS\Core\Object;

class Driver extends Object implements DriverInterface{
	private $data;
	const PHP_EXTENSION = 'pdo_pgsql';
	const ERR_TABLE_NOT_EXIST = "42P01";
	const MIN_VERSION = "8.3";

	public function check(){
		if (!extension_loaded(self::PHP_EXTENSION)) {
			throw new \RuntimeException(
				$this->tr("Database connection error, @s extension is not loaded", self::PHP_EXTENSION)
			);
		}
	}

	public function prepareConnection(\PDO $connection){

	}

	public function setup(array $dbData){
		$this->data['name'] = 'pgsql'; 

		if( isset($dbData['schema']) ){
			$this->data['schema'] = $this->prepareSql($dbData['schema']); 
		}else{
			$this->data['schema'] = 'public';
		}
	}

	public function __get($name){
		if( isset($this->data[$name]) ){
			return $this->data[$name];
		}
		return NULL;
	}

	public function __set($name, $value){
		return false;
	}
}
?>