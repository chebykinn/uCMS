<?php
/**
 *
 * uCMS Event System
 * @package uCMS
 * @since uCMS 1.3
 * @version uCMS 1.3
 *
*/
class uEvents{
	
	public static $events = array();

	/**
	 *
	 * Fire the event with given $args
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	public static function do_actions($event, $args = array()){
		if(isset(self::$events[$event])){
			foreach(self::$events[$event] as $handler){
				call_user_func_array($handler, $args);
			}
		}
	}

	/**
	 *
	 * Add action to event by anonymous function
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	public static function add_action($event, Closure $handler){
		self::$events[$event][] = $handler;
	}

	/**
	 *
	 * Add action to event by function name
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	public static function bind_action($event, $handler){
		if(is_callable($handler))
			self::$events[$event][] = $handler;
	}

	/**
	 *
	 * Remove all actions attached to events
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function remove_actions(){
		self::$events = array();
	}
}
?>