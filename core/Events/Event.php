<?php
/**
 *
 * uCMS Event
 * @package uCMS
 * @since uCMS 1.3
 * @version uCMS 2.0
 *
*/
namespace uCMS\Core\Events;
class Event{
	private $name;
	private $args = array();
	private static $actions = array();

	public function __construct($name, $args = array()){
		$this->name = $name;
		$this->args = $args;
	}

	/**
	 *
	 * Fire the event with given $args
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 2.0
	 * @return void
	 *
	*/
	public function fire($args = array()){
		if( isset(self::$actions[$this->name]) ){
			$args = array_merge($this->args, $args);
			foreach(self::$actions[$this->name] as $handler){
				call_user_func_array($handler, $args);
			}
		}
	}

	/**
	 *
	 * Add action to event by anonymous function
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 2.0
	 * @return void
	 *
	*/
	public static function AddAction($event, \Closure $handler){
		self::$actions[$event][] = $handler;
	}

	/**
	 *
	 * Add action to event by function name
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 2.0
	 * @return void
	 *
	*/
	public static function AddActionCallback($event, $handler){
		if( is_callable($handler) ){
			self::$actions[$event][] = $handler;
		}
	}

	/**
	 *
	 * Remove all actions attached to events
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 2.0
	 * @return void
	 *
	*/
	public static function RemoveActions(){
		self::$actions = array();
	}
}
?>