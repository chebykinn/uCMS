<?php
namespace uCMS\Core\Admin;
use uCMS\Core\Page;
use uCMS\Core\Extensions\Users\User;
use uCMS\Core\Extensions\Extension;
class ManagePage{
	private $adminAction;
	private $actions = [];
	private $sections = [];
	private $currentSection = '';

	public function __construct(){
		$this->adminAction = ControlPanel::GetBaseAction();
		$this->currentSection = Page::GetCurrent()->getKeyValue($this->adminAction);
	}

	public function addSection($name, $permission, $title){
		if( !isset($this->sections[$name]) ){
			$this->sections[$name] = ['name' => $name, 'permission' => $permission, 'title' => $title];
		}
	}

	public function inSection($name){
		if( !isset($this->sections[$name]) ) return false;
		return ($this->currentSection === $name);
	}

	public function checkSection(){
		$name = $this->currentSection;
		$exitPage = Page::ControlPanel($this->adminAction);
		if( empty($name) ) return true;
		if( !isset($this->sections[$name]) ){
			$exitPage->go();
		}
		$user = User::Current();
		if( !$user->can($this->sections[$name]['permission']) ){
			$exitPage->go();
		}
	}

	public function getSection(){
		return $this->currentSection;
	}

	public function getSectionPage($section = ""){
		return Page::ControlPanel($this->adminAction.(!empty($section) ? '/'.$section : ''));
	}

	public function printSectionTitle(){
		if( empty($this->currentSection) || !isset($this->sections[$this->currentSection]) ) return false;
		print '<div class="section-title"><h3>'.$this->sections[$this->currentSection]['title'].'</h3></div>';
	}


	public function addAction($name, $permission, $callback, $args = []){
		$this->actions[$name] = ['callback' => $callback, 'args' => $args, 'permission' => $permission];
	}

	public function doActions(){
		$name = Page::GetCurrent()->getKeyValue($this->adminAction);
		$exitPage = Page::ControlPanel($this->adminAction);

		if( !empty($this->actions[$name]) ){
			$key = htmlspecialchars(Page::GetCurrent()->getKeyValue($name));
			$user = User::Current();
			if( empty($key) || !$user->can($this->actions[$name]['permission']) || !is_callable($this->actions[$name]['callback']) ){
				$exitPage->go();
				return false;
			}
			call_user_func_array($this->actions[$name]['callback'], array_merge([$key], $this->actions[$name]['args']));
			
			$exitPage->go();
		}
	}

	public function addButton($title, Page $link, $class = ""){
		if( empty($class) ) $class = "button";
		print '<div class="section-button"><a class="'.$class.'" href="'.$link.'" title="'.$title.'">'.$title.'</a></div>';
	}

	public function editForm($data){

	}

	public function add($p){

	}

	public function update($p){

	}

	public function delete($p){

	}
}
?>