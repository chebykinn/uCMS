<?php
class Language{
	private static $instance;
	private $langStrings;

	public static function getInstance(){
		if ( is_null( self::$instance ) ){
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct(){
		
		//$this->load($langFile);
	}

	public function load($langName){
		$set = setlocale(LC_ALL, $langName.".utf8");
		putenv('LC_ALL=en_US.utf8');
		if($set === false){
			putenv('LC_ALL=en_US.utf8');
			setlocale(LC_ALL, "en_US.utf8");	
		} // Setting locale for date and other stuff
		$langFile = LANGUAGES_PATH.$langName.'/core.po';
	}

	public function loadStrings($langFile){
		if( file_exists($langFile) ){
			// parse headers
			$msgid = "";
			$strings = file($langFile);
			foreach ($strings as $string) {
				$match = array();
				if( preg_match("/msgid \"(.*)\"/", $string, $match) && !empty($match[1]) ){
					$msgid = $match[1];
				}
				else if( preg_match("/msgstr \"(.*)\"/", $string, $match) && !empty($match[1]) && !empty($msgid) ){
					$this->langStrings[$msgid] = $match[1];
				}
			}
		}
	}

	public function get($string, $args = array()){
		// $string = $this->getFromPO($string);
		$string = !empty($this->langStrings[$string]) ? $this->langStrings[$string] : $string;
		$args = func_get_args();
		$args = array_slice($args, 1);
		if( count($args) > 0 ){
			foreach ($args as $arg) {
				$patt[] = "/@s/";
			}
			$string = preg_replace($patt, $args, $string, 1);
		}
		return $string;
	}

	private function parseGettextPO(){
		// $file = 
	}

	private function getFromPO($string){

	}
}
?>