<?php
class CurrentUser extends User{
	private static $instance;
	
	public static function getInstance(){
		if ( is_null( self::$instance ) ){
			self::$instance = new self();
		}
		return self::$instance;
	}
}
?>