<?php
namespace uCMS\Core;
class Form{
	private $fields = array();
	private $name;
	private $owner;
	private $action;
	private $method;
	private $started = false;
	private $class = "";
	private $submitCaption = "";
	private static $types = array();

	public static function Init(){
		self::$types = array(
			'button',
			'checkbox',
			'file',
			'hidden',
			'image',
			'password',
			'radio',
			'reset',
			'submit',
			'text',
			'color',
			'date',
			'datetime',
			'datetime-local',
			'email',
			'number',
			'range',
			'search',
			'tel',
			'time',
			'url',
			'month',
			'week',
			'textarea'
		);
	}

	public function __construct($name, $action = "", $submitCaption = "", $method = "POST", $class = ""){
		$this->name = htmlspecialchars(strip_tags($name));
		$this->action = strip_tags($action); // TODO: check
		$this->method = mb_strtolower($method) === "post" ? "POST" : "GET";
		$this->owner = Tools::GetCurrentOwner();
		$this->class = !empty($class) ? htmlspecialchars(strip_tags($class)) : $this->name;
		if( empty($submitCaption) ){
			$submitCaption = tr('Submit');
		}
		$this->submitCaption = $submitCaption;
	}

	public function addHiddenField($name, $value){
		$this->addField($name, 'hidden', "", "", $value, "", false);
	}

	public function addField($name, $type, $title = "", $description = "", $defaultValue = "", $placeholder = "", $require = true){
		$type = in_array($type, self::$types) ? $type : "text";
		$this->fields[$name] = array(
			'name' => htmlspecialchars(strip_tags($name)),
			'type' => $type,
			'title' => strip_tags($title),
			'defaultValue' => htmlspecialchars(strip_tags($defaultValue)),
			'description' => strip_tags($description, '<a><p><br>'),
			'require' => (bool)$require,
			'placeholder' => htmlspecialchars(strip_tags($placeholder)),
			'printed' => false
			);
	}

	public function addSelectField($list, $name, $title = "", $description = "", $defaultValue = "", $size = 1, $require = true){
		if( is_array($list) ){
			$this->fields[$name] = array(
			'name' => htmlspecialchars(strip_tags($name)),
			'type' => 'select',
			'title' => strip_tags($title),
			'defaultValue' => htmlspecialchars(strip_tags($defaultValue)),
			'description' => strip_tags($description, '<a><p><br>'),
			'require' => (bool)$require,
			'placeholder' => "",
			'list' => $list,
			'size' => (int) $size,
			'printed' => false
			);
		}
		return false;
	}

	public function printHeader(){
		// TODO: enctype
		if( !$this->started ){
			print "<form action=\"{$this->action}\" method=\"{$this->method}\" class=\"ucms-form {$this->class}\"
			accept-charset=\"UTF-8\" enctype=\"multipart/form-data\">";
			print "<div class=\"{$this->name}-wrapper\">";
			$this->started = true;
		}
	}

	public function render($name = ""){
		$this->printHeader();
		if( !empty($name) ){
			if( isset($this->fields[$name]) ){
				$fields = array($this->fields[$name]);
			}
		}else{
			$this->addField($this->name, 'submit', "", "", $this->submitCaption);
			$fields = $this->fields;

		}
		$amount = count($this->fields);
		$c = 0;
		foreach ($fields as &$field) {
			if( !$field['printed'] ){
				$require = $field['require'] ? ' required' : ''; // fix checkbox
				$placeholder = !empty($field['placeholder']) ? ' placeholder="'.$field['placeholder'].'"' : '';
				if( $field['type'] != 'hidden' ){
					print "<div class=\"form-item form-item-{$field['name']}\">";
				}

				if( !empty($field['title']) ){
					print "<label for=\"{$field['name']}\">{$field['title']}</label>\n";
				}
				switch ($field['type']) {
					case 'textarea':
						print "<textarea name=\"{$field['name']}\"$require>{$field['defaultValue']}</textarea>\n";
					break;

					case 'select':
						$size = $field['size'] > 1 ? " size=\"{$field['size']}\"" : "";
						print "<select name=\"{$field['name']}\"$size$require>";
						foreach ($field['list'] as $value => $title) {
							$selected = $value === $field['defaultValue'] ? " selected" : "";
							print "<option value=\"$value\"$selected>$title</option>\n";
						}
						print "</select>";
					break;
					
					default:
						print "<input type=\"{$field['type']}\" name=\"{$field['name']}\" value=\"{$field['defaultValue']}\"$placeholder$require>\n";
					break;
				}
				if( !empty($field['description']) ){
					print "<div class=\"field-description\">{$field['description']}</div>\n";
				}

				if( $field['type'] != 'hidden' ){
					print '</div>';
				}
				$field['printed'] = true;
				$c++;
			}
		}
		if( $amount === $c ) echo "</div></form>";
	}
}
?>
