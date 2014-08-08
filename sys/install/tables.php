<?php
$tables = array(
		UC_PREFIX."attempts",
		UC_PREFIX."categories", 
		UC_PREFIX."comments", 
		UC_PREFIX."groups", 
		UC_PREFIX."messages", 
		UC_PREFIX."pages", 
		UC_PREFIX."posts", 
		UC_PREFIX."users", 
		UC_PREFIX."settings", 
		UC_PREFIX."usersinfo",
		UC_PREFIX."links");

$exfields = array();

$attempts[0] = array(
	"ip", 
	"date", 
	"times");
$attempts[1] = array(
	"varchar(25) NOT NULL", 
	"datetime NOT NULL DEFAULT '2012-01-01 00:00:00'", 
	"int(11) NOT NULL DEFAULT '1'");

$categories[0] = array(
	"id", 
	"name", 
	"alias", 
	"posts",
	"parent",
	"sort"); 
$categories[1] = array(
	"int(11) NOT NULL AUTO_INCREMENT", 
	"varchar(75) NOT NULL", 
	"varchar(75) NOT NULL", 
	"int(11) NOT NULL",
	"int(11) NOT NULL",
	"int(11) NOT NULL"); 

$comments[0] = array(
	"id", 
	"post", 
	"comment", 
	"author", 
	"approved", 
	"date",
	"parent",
	"ip",
	"email",
	"rating");
$comments[1] = array(
	"int(11) NOT NULL AUTO_INCREMENT", 
	"int(11) NOT NULL", 
	"longtext NOT NULL",
	"varchar(75) NOT NULL",
	"int(11) NOT NULL", 
	"datetime NOT NULL DEFAULT '2012-01-01 00:00:00'",
	"int(11) NOT NULL",
	"varchar(15) NOT NULL",
	"varchar(75) NOT NULL",
	"int(11) NOT NULL");

$groups[0] = array(
	"id", 
	"name", 
	"alias", 
	"permissions");
$groups[1] = array(
	"int(11) NOT NULL AUTO_INCREMENT", 
	"varchar(75) NOT NULL", 
	"varchar(75) NOT NULL", 
	"longtext NOT NULL");

$messages[0] = array(
	"id", 
	"author", 
	"receiver", 
	"date", 
	"text", 
	"readed");
$messages[1] = array(
	"int(11) NOT NULL AUTO_INCREMENT", 
	"varchar(75) NOT NULL", 
	"varchar(75) NOT NULL", 
	"datetime NOT NULL DEFAULT '2012-01-01 00:00:00'", 
	"text NOT NULL", 
	"int(11) NOT NULL DEFAULT '0'");

$pages[0] = array(
	"id", 
	"title", 
	"alias", 
	"author", 
	"body", 
	"publish", 
	"date",
	"parent",
	"sort");
$pages[1] = array(
	"int(11) NOT NULL AUTO_INCREMENT", 
	"varchar(75) NOT NULL", 
	"varchar(75) NOT NULL", 
	"varchar(75) NOT NULL", 
	"longtext NOT NULL", 
	"int(11) NOT NULL", 
	"datetime NOT NULL DEFAULT '2012-01-01 00:00:00'",
	"int(11) NOT NULL",
	"int(11) NOT NULL");

$posts[0] = array(
	"id", 
	"title", 
	"body", 
	"keywords", 
	"publish", 
	"alias", 
	"author", 
	"category", 
	"comments", 
	"date");
$posts[1] = array(
	"int(11) NOT NULL AUTO_INCREMENT", 
	"varchar(75) NOT NULL", 
	"longtext NOT NULL", 
	"text NOT NULL", 
	"int(11) NOT NULL DEFAULT '0'", 
	"varchar(75) NOT NULL", 
	"varchar(75) NOT NULL", 
	"int(11) NOT NULL", 
	"int(11) NOT NULL", 
	"datetime NOT NULL DEFAULT '2012-01-01 00:00:00'");

$users[0] = array(
	"id", 
	"login", 
	"password", 
	"group", 
	"avatar", 
	"email", 
	"activation", 
	"date", 
	"session_hash", 
	"regip", 
	"logip", 
	"online", 
	"lastlogin");
$users[1] = array(
	"int(255) NOT NULL AUTO_INCREMENT", 
	"varchar(255) NOT NULL", 
	"varchar(75) NOT NULL", 
	"int(11) NOT NULL DEFAULT '4'", 
	"varchar(255) NOT NULL", 
	"varchar(255) NOT NULL", 
	"int(1) NOT NULL", 
	"datetime NOT NULL DEFAULT '2012-01-01 00:00:00'", 
	"varchar(40) NOT NULL", 
	"varchar(15) NOT NULL", 
	"varchar(15) NOT NULL", 
	"int(11) NOT NULL", 
	"datetime NOT NULL DEFAULT '0000-00-00 00:00:00'");

$settings[0] = array(
	"id", 
	"name", 
	"value",
	"update",
	"owner");
$settings[1] = array(
	"int(11) NOT NULL AUTO_INCREMENT", 
	"varchar(75) NOT NULL", 
	"longtext NOT NULL",
	"datetime NOT NULL DEFAULT '2012-01-01 00:00:00'",
	"varchar(255) NOT NULL DEFAULT 'system'");

$usersinfo[0] = array(
	"id", 
	"user_id", 
	"name", 
	"value", 
	"required", 
	"update");
$usersinfo[1] = array(
	"int(11) NOT NULL AUTO_INCREMENT", 
	"int(11) NOT NULL", 
	"varchar(255) NOT NULL", 
	"longtext NOT NULL", 
	"int(11) NOT NULL",
	"datetime NOT NULL DEFAULT '2012-01-01 00:00:00'");

$links[0] = array(
	"id",
	"name",
	"publish",
	"url",
	"description",
	"author",
	"target",
	"date");

$links[1] = array(
	"int(11) NOT NULL AUTO_INCREMENT",
	"varchar(75) NOT NULL",
	"varchar(75) NOT NULL",
	"varchar(75) NOT NULL",
	"varchar(75) NOT NULL",
	"varchar(75) NOT NULL",
	"varchar(75) NOT NULL",
	"datetime NOT NULL DEFAULT '2012-01-01 00:00:00'");

$exfields[0] = $attempts;
$exfields[1] = $categories;
$exfields[2] = $comments;
$exfields[3] = $groups;
$exfields[4] = $messages;
$exfields[5] = $pages;
$exfields[6] = $posts;
$exfields[7] = $users;
$exfields[8] = $settings;
$exfields[9] = $usersinfo;
$exfields[10] = $links;

$add_tables = array();
for($i = 0; $i < count($exfields); $i++) {
	$add_tables[$i] = "CREATE TABLE IF NOT EXISTS `$tables[$i]` (\n";
	$j = 0;
	foreach ($exfields[$i][0] as $field) {
		$add_tables[$i] .= "`$field` ".$exfields[$i][1][$j].",\n";
		$j++;
	}

	if($tables[$i] == UC_PREFIX."attempts") {
		$key = 'ip';
	}else {
		$key = 'id';
	}
	$add_tables[$i] .= "PRIMARY KEY (`$key`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;";
}
?>