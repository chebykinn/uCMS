<?php
/**
 *
 * uDB class, functions to work with database.
 * @package uCMS
 * @since uCMS 1.0
 * @version uCMS 1.3
 *
*/
if(!defined("UDB_SHOW_QUERY"))
	define("UDB_SHOW_QUERY", false);

class uDB{
	var $server;
	var $login;
	var $password;
	var $db;
	var $con;
	var $count;

	/**
	 *
	 * Connect to database
	 * @package uCMS
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return connection object or false
	 *
	*/
	function db_connect($server, $login, $password, $db){
		global $install;
		$this->count = 0;
		$this->server = $server;
		$this->login = $login;
		$this->password = $password;
		$this->db = $db;
		if(!UCMS_DEBUG)
			$con = @mysqli_connect($this->server, $this->login, $this->password, $this->db);
		else $con = mysqli_connect($this->server, $this->login, $this->password, $this->db);
		if(!$con and !$install or empty($db)){
			$this->panic(1);
			return false;
		}elseif($con){
			if(function_exists('mysqli_set_charset')){
				mysqli_set_charset($con, "utf8");
			}else{
				@mysqli_query($con, "SET NAMES utf8 COLLATE utf8_general_ci");
			}
			$this->con = $con;
			return $con;
		}else return false;		
	}

	/**
	 *
	 * Disconnect from database
	 * @package uCMS
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function db_disconnect($con){
		$db = $this->db;
		$query = 'SHOW FULL PROCESSLIST';
    	$result = @mysqli_query($con, $query);
    	while (($check = @mysqli_fetch_assoc($result))){
        	if ($check['db'] != $db) continue;
        	if ($check['Command'] != 'Sleep') continue;
       		@mysqli_query($con, 'KILL ' . $check['id']);
    	}	
		@mysqli_close($con);
	}

	/**
	 *
	 * Send query to connected database
	 * @package uCMS
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return bool
	 *
	*/
	function query($sql, $no_die = false){
		if($sql == "") return false;
		$query = @mysqli_query($this->con, $sql);
		if(!$query){
			if(!$no_die){ 
				$this->panic();		
			}
			return false;
		}else{
			if(UDB_SHOW_QUERY)
				echo "<br><pre>".$sql."</pre><br>";
			$this->count++;
			return true;

		}
	}

	/**
	 *
	 * Send query to connected database and get object of it
	 * @package uCMS
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return query object
	 *
	*/
	function get_query($sql){
		if($sql == "") return false;
		$query = mysqli_query($this->con, $sql);
		if(!$query){
			$this->panic();	
		}
		return $query;
	}

	/**
	 *
	 * Get one row from $sql query
	 * @package uCMS
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return array
	 *
	*/
	function get_row($sql, $no_die = false){
		if($sql == "") return false;
		$query = @mysqli_query($this->con, $sql);
		if(!$query){
			if(!$no_die){ 
				$this->panic();		
			}
		}
		if($row = @mysqli_fetch_array($query)){
			if(UDB_SHOW_QUERY)
				echo "<br><pre>".$sql."</pre><br>";
			$this->count++;
			return $row;
		}
		else return false;
	}

	/**
	 *
	 * Get selected rows from $sql query
	 * @package uCMS
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return array
	 *
	*/
	function get_rows($sql){
		if($sql == "") return false;
		$query = @mysqli_query($this->con, $sql);
		if(!$query){
			$this->panic();
		}
		if(@mysqli_num_rows($query) != 0){
			$data = array();
			$i = 0;
			while($row = @mysqli_fetch_array($query)){
				$data[$i] = $row;
				$i++;
			}
			if(UDB_SHOW_QUERY)
				echo "<br><pre>".$sql."</pre><br>";
			$this->count++;
			return $data;
		}else return false;
	}

	/**
	 *
	 * Get count of rows from $sql query
	 * @package uCMS
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return int
	 *
	*/
	function num_rows($sql, $no_die = false){
		if($sql == "") return false;
		$query = @mysqli_query($this->con, $sql);
		if(!$query){
			if(!$no_die){ 
				$this->panic();		
			}
		}
		$nums = @mysqli_num_rows($query);
		if(UDB_SHOW_QUERY)
			echo "<br><pre>".$sql."</pre><br>";
		$this->count++;
		return $nums;
		
	}

	/**
	 *
	 * Prepare $value for SQL query
	 * @package uCMS
	 * @since uCMS 1.0
	 * @version uCMS 1.3
	 * @return value
	 *
	*/
	function parse_value($value){
		if($value == "") return false;
		$value = @mysqli_real_escape_string($this->con, $value);
		$value = addcslashes($value, '%');
		return $value;
	}

	/**
	 *
	 * Get number of performed queries to current database 
	 * @package uCMS
	 * @since uCMS 1.2
	 * @version uCMS 1.3
	 * @return int
	 *
	*/
	function get_queries_count(){
		return $this->count;
	}

	/**
	 *
	 * [INTERNAL] Throws MySQL errors
	 * @package uCMS
	 * @access private
	 * @since uCMS 1.1
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function panic($err_lvl = 2){
		global $install;
		if(!isset($install)){
			if(mysqli_errno($this->con) == 2006 or mysqli_errno($this->con) == 2013){
				die(require ABSPATH.ERROR_TEMPLATES_PATH.'udb.php');
			}
			switch ($err_lvl) {
				case 1:
					if(UCMS_DEBUG){
						echo "<br><b>Can't connect to MySQL database, error #".mysqli_connect_errno().": ".mysqli_connect_error()."</b><br><br>";
						echo "<b>Debug Trace:</b><br>";
						echo "<pre>";
						debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
						echo "</pre>";
						echo "<br>Default error message loaded from <b>".ERROR_TEMPLATES_PATH."udb.php:</b><br><br>";
						die(require ABSPATH.ERROR_TEMPLATES_PATH.'udb.php');
					}else{
						if(mysqli_connect_errno() == 1045){
							header("Location: /sys/install/index.php");
							exit;
						}else die(require ABSPATH.ERROR_TEMPLATES_PATH.'udb.php');
					}
						
				break;
				
				case 2:
					if(UCMS_DEBUG){
						echo "<br><b>MySQL Error #".mysqli_errno($this->con).': '.mysqli_error($this->con).".<br><br> Debug Trace:</b><br>"; 
						echo "<pre>";
						debug_print_backtrace();
						echo "</pre>";
						die();
					}else{
						if(mysqli_errno($this->con) === 1146){
							global $uc_tables;
							$table = mysqli_error($this->con);
							$table = str_replace("Table '".$this->db.'.', "", $table);
							$table = str_replace("' doesn't exist", "", $table);
							if(in_array($table, $uc_tables)){
								header("Location: /sys/install/index.php");
								exit;
							}else echo "<b>MySQL Error #".mysqli_errno($this->con).': '.mysqli_error($this->con)."</b>";
						}else echo "<b>MySQL Error #".mysqli_errno($this->con).': '.mysqli_error($this->con)."</b>";
					} 
				break;
	
				default:
					
				break;
			}
		}
	}

	/**
	 *
	 * Get value from $sql query
	 * @package uCMS
	 * @access public
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function get_val($sql, $no_die = false){
		if($sql == "") return false;
		$query = mysqli_query($this->con, $sql);
		if(!$query){
			if(!$no_die){ 
				$this->panic();		
			}
		}
		if($row = mysqli_fetch_array($query)){
			if(UDB_SHOW_QUERY)
				echo "<br><pre>".$sql."</pre><br>";
			$this->count++;
			return isset($row[0]) ? $row[0] : NULL;
		}
		else return NULL;
	}

	/**
	 *
	 * [INTERNAL] Get MySQL version
	 * @package uCMS
	 * @access private
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return string
	 *
	*/
	function mysql_version() {
		if(isset($this->con))
			return preg_replace( '/[^0-9.].*/', '', mysqli_get_server_info($this->con));
	}
}
?>