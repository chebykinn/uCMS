<?php
use uCMS\Core\Setting;
use uCMS\Core\Extensions\ExtensionHandler;
$limit = (int)Setting::Get('last_added_limit');
$models = array();
$names = json_decode(Setting::Get('last_added_models'), true);
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