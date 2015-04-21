<?php
$data = Settings::get('extentions');
$data = explode(',', $data);
$extentions = new AdminPage($data, 'core');
$extentions->printTable();
?>