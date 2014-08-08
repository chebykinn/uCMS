<?php
function welcome(){
	?>
	<h2>Установка uCMS</h2>
	<br><br>
	Добро пожаловать в uCMS! Сейчас вам предстоит пройти несколько пунктов, и установка будет завершена.<br><br>
	<form action="index.php" method="post"><input class="button" type="submit" value="Приступим!" name="continue"></form>
	<?php
}

function make_config(){
	?>
		<h2>Установка uCMS :: подключение к базе данных</h2><br>
		<form action="setup.php" method="post">
		<table style="margin: 0 auto; text-align: left; border-spacing: 10px;">
		<tr>
			<td><label>Сервер базы данных:</label></td>
			<td><input type="text" name="dbserver" value="localhost" required></td>
			<td style="font-size: 10pt;"> - В большинстве случаев это localhost.</td>
		</tr>
		<tr>
			<td><label>Логин:</label></td>
			<td><input type="text" name="dbuser" value="username" required></td>
			<td style="font-size: 10pt;"> - Логин пользователя базы данных, предоставляется хостингом.</td>
		</tr>
		<tr>
			<td><label>Пароль:</label></td> 
			<td><input type="text" name="dbpass" value="password"></td>
			<td style="font-size: 10pt;"> - Пароль пользователя базы данных, предоставляется хостингом.</td>
		</tr>
		<tr>
			<td><label>Название базы данных:</label></td>
			<td><input type="text" name="dbname" value="mydatabase" required></td>
			<td style="font-size: 10pt;"> - База данных, куда вы хотите установить uCMS.</td>
		</tr>
		<tr>
			<td><label>Префикс таблиц:</label></td>
			<td><input type="text" name="uc_prefix" value="uc_" required></td>
			<td style="font-size: 10pt;"> - Используется для того, чтобы устанавливать несколько копий uCMS в одну базу данных.</td>
		</tr>
		</table><br>
		<input name="config" class="button" type="submit" value="Далее">
		</form>
	<?php
	}

function make_tables(){
	?>
		<h2>Установка uCMS</h2>
		<br><br>Таблиц не существует, надо создать!<br>
		<br><form action="setup.php" method="post"><input class="button" type="submit" name="add-tables" value="Давайте!"></form>
	<?php
}

function no_connect(){
	unlink('../../config.php');
	?>
	<h2>Установка uCMS :: ошибка подключения к базе данных</h2><br>
	Кажется вы ошиблись при вводе логина или пароля к базе данных и uCMS не удалось подключиться.<br><br>
	<form action="index.php" method="post"><input class="button" type="submit" value="Еще раз" name="continue"></form>
	<?php
}

function fine(){
	echo '<h2>Установка uCMS</h2><br>Все в порядке, uCMS уже установлена, если вы хотите переустановить её, то очистите таблицы.<br><br><a class="button" href="../../" >На главную</a>';
}

function fill_settings(){
	$domain = 'http://'.$_SERVER['HTTP_HOST'];
	?>
	<h2>Установка uCMS :: Основные настройки</h2><br>
	<b>Еще пара пунктов и все, честно!</b><br><br>
		<form action="setup.php" method="post">
		<table style="margin: 0 auto; text-align: left; border-spacing: 10px;">
		<tr>
			<td><label>Название сайта:</label></td>
			<td><input type="text" name="site_name" value="Сайт на uCMS" required></td>
			<td style="font-size: 10pt;"> - Назовите свой сайт, например: "Сайт Ивана".</td>
		</tr>
		<tr>
			<td><label>Описание сайта:</label></td>
			<td><input type="text" name="site_description" value="Это Ваш сайт" required></td>
			<td style="font-size: 10pt;"> - Опишите свой сайт, например: "Крутой сайт обо мне".</td>
		</tr>
		<tr>
			<td><label>Заголовок сайта:</label></td> 
			<td><input type="text" name="site_title" value="Сайт на uCMS" required></td>
			<td style="font-size: 10pt;"> - Отображается в заголовке страницы в браузере, например: "Сайт Ивана - крутейший сайт на свете!".</td>
		</tr>
		<tr>
			<td><label>Домен сайта:</label></td>
			<td><input type="text" name="domain" value="<?php echo $domain; ?>" required></td>
			<td style="font-size: 10pt;"> - Домен, на котором находится сайт, если он совпадает с тем, что в адресной строке, то не изменяйте его.</td>
		</tr>
		<tr>
			<td><label>Каталог для uCMS(если есть):</label></td> 
			<td><input type="text" name="dir"></td>
			<td style="font-size: 10pt;"> - Папка в которой лежит uCMS - <?php echo $domain; ?>/ucms - папка "/ucms". Если нет, то оставьте пустым.</td>
		</tr>
		</table>
		<br><input name="fill-tables" class="button" type="submit" value="Далее">
		</form>
	<?php
}

function fill_users(){
	?>
	<h2>Установка uCMS :: Создание администратора</h2><br>
	<b>Давайте создадим вам администратора!</b><br><br>
		<form action="setup.php" method="post">
		<table style="margin: 0 auto; text-align: left; border-spacing: 10px;">
		<tr>
			<td><label>E-mail:</label></td>
			<td><input type="text" name="setup-email" value="admin@<?php echo $_SERVER['HTTP_HOST']; ?>" required></td>
			<td style="font-size: 10pt;"> - Ваш e-mail, на него будут приходить все уведомления.</td>
		</tr>
		<tr>
			<td><label>Логин:</label></td>
			<td><input type="text" name="setup-login" value="admin" required></td>
			<td style="font-size: 10pt;"> - Назовите администратора.</td>
		</tr>
		<tr>
			<td><label>Пароль:</label></td> 
			<td><input type="password" name="setup-password" placeholder="p@$sW00Rd!" required></td>
			<td style="font-size: 10pt;"> - Задайте сложный пароль: безопасность сайта зависит от этого.</td>
		</tr>
		</table><br>
		<input name="fill-tables" class="button" type="submit" value="Далее">
		</form>
	<?php
}

function success(){
	echo '<h2>Все!</h2><br>Ура, uCMS успешно установлена, теперь Вы можете использовать Ваш сайт!<br><br><a class="button" href="../../" >Хочу на сайт!</a>';
	unset($_SESSION['success']);
}
?>