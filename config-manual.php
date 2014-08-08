<?php			
/*Добро пожаловать в исходный код uCMS! Этот файл нужно беречь!*/

define(UC_PREFIX, "uc_"); //Префикс для таблиц, если вы хотите установить несколько копий uCMS в одну базу данных, то поставьте свой

define(DB_SERVER, "localhost"); //Сервер базы данных

define(DB_USER, "login"); //Логин

define(DB_PASSWORD, "password"); //Пароль

define(DB_NAME, "ucms_database"); //Имя базы данных

if(!defined(ABSPATH))
	define(ABSPATH, dirname(__FILE__)."/"); //Абсолютный путь к файлам CMS относительно config.php

define(UCMS_DEBUG, false); //Режим отладки uCMS. Рекомендуется для разработчиков

/*Загрузка необходимого*/
require_once ABSPATH."sys/load.php";
?>