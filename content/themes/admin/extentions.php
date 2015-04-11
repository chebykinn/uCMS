<?php
$data = get_setting('extentions');
$data = explode(',', $data);
$extentions = new AdminPage($data, 'core');
$extentions->printTable();
?>