<div class="sidebar">
	<ul>
		<?php
			$links[0] = array( //названия пунктов
				"Главная", 
				"", 
				"Страницы", 
				"Посты", 
				"Категории", 
				"Комментарии", 
				"", 
				"Пользователи", 
				"Группы", 
				"", 
				"Ссылки",
				"",
				"Настройки",
				"Уведомления",
				"", 
				"Внешний вид", 
				"Редактор",
				"",
				"Виджеты",
				"Редактор");
			$links[1] = array( //путь к файлу пункта
				"index.php", 
				"", 
				"pages.php", 
				"posts.php", 
				"categories.php", 
				"comments.php", 
				"", 
				"users.php", 
				"groups.php",
				"", 
				"links.php",
				"",
				"settings.php",
				"notifications.php",
				"",
				"themes.php", 
				"editor.php?type=themes",
				"",
				"widgets.php",
				"editor.php?type=widgets");
			$links[2] = array("1", "0", "3", "1", "1", "2", "4", "4", "4", "5", "5", "5", "5", "5", "5", "5", "5", "5", "5", "5"); //номер модуля: 1, 2, 3, 4, 5 - посты, комменты, страницы, пользователи, все вместе
			$links[3] = array("1", "2", "2", "2", "6", "2", "2", "2", "7", "7", "7", "7", "7", "7", "7", "7", "7", "7", "7", "7"); //уровень доступа от 0 до 7
			$links[4] = array( //показывать пункт или нет
				true, 

				PAGES_MODULE or POSTS_MODULE, //разделитель

				PAGES_MODULE, 
				POSTS_MODULE, 
				POSTS_MODULE, 
				COMMENTS_MODULE, 

				USERS_MODULE, //разделитель

				USERS_MODULE, 
				USERS_MODULE,

				true, //разделитель

				true,

				true, //разделитель

				true,
				true, 

				THEMES_MODULE, //разделитель

				THEMES_MODULE, 
				false,

				WIDGETS_MODULE, //разделитель

				WIDGETS_MODULE,
				false);
			for($i = 0; $i < count($links[0]); $i++){
				if($user->has_access($links[2][$i], $links[3][$i]) and $links[0][$i] != ""){
					if($links[4][$i]){
						if($_SERVER['REQUEST_URI'] == UCMS_DIR."/admin/".$links[1][$i]){
							echo '<li class="selected">'; 
						}else {
							echo "<li>"; 
						}
						echo '<a href="'.$links[1][$i].'">'.$links[0][$i].'</a></li>';
					}
				}else{
					if($user->has_access($links[2][$i], $links[3][$i]) and $links[4][$i]){
						echo '<li class="sidebar-border"></li>';
					}
				}
			}
			/*<li><a href="test">Настройки</a>
		<ul>
		
			<li><a href="test2">Посты</a></li>
			<li class="selected2"><a href="test3">Комментарии</a></li>
		</ul>
		</li>*/
		?>
		<li class="sidebar-border"></li>
		
		<?php if($user->has_access(5, 7) and PHPMYADMIN_LINK != ''){ ?><li><a href="<?php echo PHPMYADMIN_LINK ?>">PHPMyAdmin</a></li><?php } ?>
		<li style="margin-top: 100%;"><a href="<?=SITE_DOMAIN.UCMS_DIR?>" >Перейти к сайту</a></li>
	</ul>
</div>