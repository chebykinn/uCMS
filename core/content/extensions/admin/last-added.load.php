<?php
use uCMS\Core\Settings;
use uCMS\Core\Extensions\ExtensionHandler;
$limit = (int)Settings::Get('last_added_limit');
$models = array();
$names = json_decode(Settings::Get('last_added_models'), true);
if ( is_array($names) ){
	foreach ($names as $title => $modelInfo) {
		if( class_exists($modelInfo['name']) ){
			$model = new $modelInfo['name']();
			$modelInfo['conditions']['limit'] = $limit;
			$modelRows = $model->find($modelInfo['conditions']);
			$extension = ExtensionHandler::Get($modelInfo['owner']);
			$template = $extension->getFilePath($modelInfo['template']);
			$models[$title] = array("rows" => $modelRows, "title" => $title, "template" => $template);
		}
	}
}
?>