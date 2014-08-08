<?php
define(UDB_SHOW_QUERY, false);

class uDB{
	var $server;
	var $login;
	var $password;
	var $db;
	var $con;
	var $count;

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
		if(!$con and !$install){
			$this->panic(1);
			return false;
		}elseif(($con and !$install) or ($con and $install)){
			mysqli_set_charset($con, "utf8");
			$this->con = $con;
			return $con;
		}else return false;		
	}

	function db_disconnect($con){
		global $user;
		$this->query("UPDATE `".UC_PREFIX."users` SET `online` = '0' WHERE UNIX_TIMESTAMP() - UNIX_TIMESTAMP(lastlogin) > 3600");
		$this->query("UPDATE `".UC_PREFIX."stats` SET `value` = '0', `update` = NOW() WHERE `id` = '1' AND UNIX_TIMESTAMP() - UNIX_TIMESTAMP(`update`) > 450");
		if(UPDATE_USER == $user->get_user_id())
			$this->query("UPDATE `".UC_PREFIX."stats` SET `value` = '0', `update` = NOW() WHERE `id` = '2'");
		$db = $this->db;
		$query = 'SHOW FULL PROCESSLIST';
    	$result = mysqli_query($con, $query);
    	while (($check = mysqli_fetch_assoc($result))){
        	if ($check['db'] != $db) continue;
        	if ($check['Command'] != 'Sleep') continue;
       		mysqli_query($con, 'KILL ' . $check['id']);
    	}	
		mysqli_close($con);
	}

	function query($sql, $no_die = false){
		$query = mysqli_query($this->con, $sql);
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

	function get_query($sql){
		$query = mysqli_query($this->con, $sql);
		if(!$query){
			$this->panic();	
		}
		return $query;
	}

	function get_row($sql){
		$query = mysqli_query($this->con, $sql);
		if(!$query){
			$this->panic();	
		}
		if($row = mysqli_fetch_array($query)){
			if(UDB_SHOW_QUERY)
				echo "<br><pre>".$sql."</pre><br>";
			$this->count++;
			return $row;
		}
		else return false;
	}

	function get_rows($sql, $mode = 1){
		$query = mysqli_query($this->con, $sql);
		if(!$query){
			$this->panic();
		}
		if(mysqli_num_rows($query) != 0){
			$data = array();
			$i = 0;
			while($row = mysqli_fetch_array($query)){
				$data[$i] = $row;
				$i++;
				if($i > 40000) break;
			}
			if(UDB_SHOW_QUERY)
				echo "<br><pre>".$sql."</pre><br>";
			$this->count++;
			return $data;
		}else return false;
	}


	function num_rows($sql, $no_die = false){
		$query = mysqli_query($this->con, $sql);
		if(!$query){
			if(!$no_die){ 
				$this->panic();		
			}
		}
		$nums = mysqli_num_rows($query);
		if(UDB_SHOW_QUERY)
			echo "<br><pre>".$sql."</pre><br>";
		$this->count++;
		return $nums;
		
	}

	function parse_value($value){
		$value = mysqli_real_escape_string($this->con, $value);
		return $value;
	}

	function get_queries_count(){
		return $this->count;
	}

	function panic($err_lvl = 2){
		switch ($err_lvl) {
			case 1:
				if(UCMS_DEBUG){
					echo "<br><b>Can't connect to MySQL database, error #".mysqli_connect_errno().": ".mysqli_connect_error()."</b><br><br>";
					echo "<b>Debug Trace:</b><br>";
					echo "<pre>";
					debug_print_backtrace();
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
						global $tables;
						$table = mysqli_error($this->con);
						$table = str_replace("Table '".$this->db.'.', "", $table);
						$table = str_replace("' doesn't exist", "", $table);
						if(in_array($table, $tables)){
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
?>