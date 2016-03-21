<?php
/**
* This file contains implementation of driver interface for MySQL and MariaDB.
*
* @author Ivan Chebykin
* @author ivan4b69@gmail.com
* @since 2.0
*/
namespace uCMS\Core\Database\Driver\mysql;

use uCMS\Core\Database\Driver\DriverInterface;
use \uCMS\Core\Object;

class Driver extends Object implements DriverInterface{
	private $data;
	const PHP_EXTENSION = 'pdo_mysql';
	const ERR_TABLE_NOT_EXIST = "42S02";
	const MIN_VERSION = "5.1";

	public function check(){
		if (!extension_loaded(self::PHP_EXTENSION)) {
			throw new \RuntimeException(
				$this->tr("Database connection error, @s extension is not loaded", self::PHP_EXTENSION)
			);
		}
	}

	public function prepareConnection(\PDO $connection){
		$connection->exec("set names utf8");
	}

	public function setup(array $dbData){
		$this->data['name'] = 'mysql';
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