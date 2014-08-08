<html>
<head>
<meta charset="utf-8">
<title>Database error / Ошибка базы данных</title>
</head>
<body>
	<h1>μCMS couldn't connect to the database. / μCMS не удалось установить соединение с базой данных.</h1>
	<h2>This could happen due to: / Это могло произойти из-за:</h2><br>
	<h3><ul>
		<li>Database server overload / Перегрузки сервера базы данных</li>
		<li>Wrong username or password for database user / Неправильные логин или пароль для пользователя базы данных</li>
		<li>Database server maintenance / Технические работы на сервере базы данных</li>
	</ul></h3><br>
	<h3>Contact your hosting tech support or try to reinstall μCMS. / Свяжитесь c технической поддержкой хостинга или попробуйте переустановить μCMS.</h3>
	<?php
		if(mysqli_connect_errno() > 0) {
			echo 'MySQL Error #'.mysqli_connect_errno().': '.mysqli_connect_error();
		}
		else{
			echo 'MySQL Error #'.mysqli_errno($this->con).': '.mysqli_error($this->con);
		}
	?>
</body>
</html>