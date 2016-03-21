<?php
/**
* This file contains interface for SQL drivers.
*
* @author Ivan Chebykin
* @author ivan4b69@gmail.com
* @since 2.0
*/
namespace uCMS\Core\Database\Driver;

/**
* This interface is used for SQL drivers.
* 
* Through this interface SQL drivers are able to do specific tasks on database connection.
*/
interface DriverInterface{

	/**
	* Driver-specific connection preparation.
	*
	* This method allows driver to do some tasks on estabilished connection
	*
	* @since 2.0
	* @param \PDO $connection Establisihed connection
	* @return void
	*/
	public function prepareConnection(\PDO $connection);

	/**
	* Perform driver check.
	*
	* Perform environment check before connecting to database.
	*
	* @since 2.0
	* @param none
	* @return void
	*/
	public function check();

	/**
	* Prepare connection data.
	*
	* Do some variables initialization with given connection data.
	*
	* @since 2.0
	* @param array $dbData Connection data from config file
	* @return void
	*/	
	public function setup(array $dbData);

	public function __get($name);

	public function __set($name, $value);
}
?>