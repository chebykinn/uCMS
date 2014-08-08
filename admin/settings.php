<?php
$title = "Настройки :: ";
include 'head.php';
include 'sidebar.php';
if(!$user->has_access(5, 7)) header("Location: index.php");
?>
<div id="content">
<?php
if($user->has_access(5, 7)){
	$ucms->settings();
?>
	<h2>Настройки</h2><br>
	<form action="settings.php" method="post">
		<table class="forms" style="width: 1000px;">
		<tr>
		<td>Название сайта:</td>
		<td><input type="text" name="site_name" value="<?php echo htmlspecialchars(SITE_NAME) ?>"></td>
		<td></td>
		</tr>
		<tr>
		<td>Описание сайта:</td>
		<td><input type="text" name="site_description" value="<?php echo htmlspecialchars(SITE_DESCRIPTION) ?>"></td>
		<td></td>
		</tr>
		<tr>
		<td>Заголовок:</td>
		<td><input type="text" name="site_additional_name" value="<?php echo htmlspecialchars(SITE_TITLE) ?>"></td>
		<td>Отображается в заголовке странице.</td>
		</tr>
		<tr>
		<td>Автор сайта:</td>
		<td><input type="text" name="site_author" value="<?php echo SITE_AUTHOR ?>"></td>
		<td>Указать кто является автором этого сайта.</td>
		</tr>
		<tr>
		<td>Включить режим техобслуживания сайта:</td>
		<td><input type="checkbox" name="ucms_maintenance" value=1 <?php if(UCMS_MAINTENANCE) echo "checked" ?>></td>
		<td>Выключить сайт для всех, кроме администраторов.</td>
		</tr>
		<tr>
		<td>Человекопонятные ссылки:</td>
		<td><input type="checkbox" name="nice_links" value=1 <?php if(NICE_LINKS) echo "checked" ?>></td>
		<td>Красивые ссылки, например: <?php echo SITE_DOMAIN; ?>/super-cool-post.</td>
		</tr>
		<?php if(NICE_LINKS){ ?>
		<tr>
		<td>Вид ссылок на посты:</td>
		<td><input type="text" name="post_sef_link" value="<?php echo POST_SEF_LINK ?>"></td>
		<td>Редактировать красивые ссылки на посты можно добавлять разный текст или дополнительную информацию о посте, <a href="http://ucms.ivan4b.ru/other/sef-links-tags">подробнее.</a></td>
		</tr>
		<tr>
		<td>Вид ссылок на страницы:</td>
		<td><input type="text" name="page_sef_link" value="<?php echo PAGE_SEF_LINK ?>"></td>
		<td>Тоже самое, что и выше, только для страниц.</td>
		</tr>
		<tr>
		<td>Префикс для категорий:</td>
		<td><input type="text" name="category_sef_prefix" value="<?php echo CATEGORY_SEF_PREFIX ?>"></td>
		<td>Можно добавить любой текст, который будет идти перед ссылкой на категорию, не оставляйте пустым.</td>
		</tr>
		<tr>
		<td>Префикс для тегов:</td>
		<td><input type="text" name="tag_sef_prefix" value="<?php echo TAG_SEF_PREFIX ?>"></td>
		<td>Тоже самое, что и выше, только для тегов.</td>
		</tr>
		<?php } ?>

		<tr>
		<td>Включить посты:</td>
		<td><input type="checkbox" name="posts_module" value=1 <?php if(POSTS_MODULE) echo "checked" ?>></td>
		<td>Включить или выключить модуль постов, если выключен, то посты выводиться не будут.</td>
		</tr>
		
		<?php if(POSTS_MODULE){ ?>
		<tr>
		<td>Комментарии к постам:</td>
		<td><input type="checkbox" name="comments_module" value=1 <?php if(COMMENTS_MODULE) echo "checked" ?>></td>
		<td>Включить или выключить комментарии.</td>
		</tr>
		<tr>
		<td>Количество постов на одной странице:</td>
		<td><input type="number" name="posts_on_page" min="1" value="<?php echo POSTS_ON_PAGE; ?>"></td>
		<td></td>
		</tr>
		<?php } ?>

		<tr>
		<td>Включить страницы:</td>
		<td><input type="checkbox" name="pages_module" value=1 <?php if(PAGES_MODULE) echo "checked" ?>></td>
		<td>Включить или выключить модуль страниц, если выключен, то страницы выводиться не будут.</td>
		</tr>

		<tr>
		<td>Включить пользователей:</td>
		<td><input type="checkbox" name="users_module" value=1 <?php if(USERS_MODULE) echo "checked" ?>></td>
		<td>Включить или выключить модуль пользователей, если выключен, то все посетители сайта будут иметь все права доступа.</td>
		</tr>

		<?php if(USERS_MODULE){ ?>
		<tr>
		<td>Аватары:</td>
		<td><input type="checkbox" name="user_avatars" value=1 <?php if(USER_AVATARS) echo "checked" ?>></td>
		<td>Включить или выключить аватары для пользователей.</td>
		</tr>
		<?php if(USER_AVATARS){ ?>
		<tr>
		<td>Размер аватара:</td>
		<td><input type="number" name="avatar_width" min="100" max ="1000" value="<?php echo AVATAR_WIDTH; ?>"> x <input type="number" name="avatar_height" min="100" max ="1000" value="<?php echo AVATAR_HEIGHT; ?>"></td>
		<td>Ширина и высота аватара пользователя.</td>
		</tr>
		<?php } ?>
		<tr>
		<td>Личные сообщения:</td>
		<td><input type="checkbox" name="user_messages" value=1 <?php if(USER_MESSAGES) echo "checked" ?>></td>
		<td>Включить или выключить обмен личными сообщениями для пользователей.</td>
		</tr>
		<tr>
		<td>Разрешить любому зарегистрироваться на сайте:</td>
		<td><input type="checkbox" name="allow_registration" value=1 <?php if(ALLOW_REGISTRATION) echo "checked" ?>></td>
		<td></td>
		</tr>
		<tr>
		<td>Запретить одинаковые e-mail'ы:</td>
		<td><input type="checkbox" name="unique_emails" value=1 <?php if(UNIQUE_EMAILS) echo "checked" ?>></td>
		<td>Запретить или разрешить повторение e-mail'ов у разных пользователей.</td>
		</tr>
		<tr>
		<td>Стандартная группа для пользователей:</td>
		<td><select name="default_group" style="width: 150px;">
			
			<?php
			$group = $udb->get_rows("SELECT `id`,`name` FROM `".UC_PREFIX."groups` ORDER BY `id` ASC");
			if(!$user->has_access(4, 6)) $j = 1;
			else $j = 0;
			for($j; $j < count($group); $j++){
				echo "<option value=\"".$group[$j]['id']."\" ".(DEFAULT_GROUP == $group[$j]['id'] ? "selected" : "").">".$group[$j]['name']."</option>";
			}
			?>
		</select></td>
		<td>Группа, куда будут попадать пользователи после регистрации.</td>
		</tr>
		<tr>
		<td>Максимальное количество попыток входа на сайт:</td>
		<td><input type="number" name="num_tries" min="0" value="<?php echo LOGIN_ATTEMPTS_NUM ?>"></td>
		<td></td>
		</tr>
		<?php } ?>
	
		<tr>
		<tr>
		<td>Включить темы:</td>
		<td><input type="checkbox" name="themes_module" value=1 <?php if(THEMES_MODULE) echo "checked" ?>></td>
		<td>Включить или выключить модуль тем, если выключен, то тема на сайте будет зафиксирована на текущей.</td>
		</tr>

		<tr>
		<td>Включить виджеты:</td>
		<td><input type="checkbox" name="widgets_module" value=1 <?php if(WIDGETS_MODULE) echo "checked" ?>></td>
		<td>Включить или выключить модуль пользователей, если выключен, то всеми виджетами нельзя будет пользоваться.</td>
		</tr>

		<tr>
		<td>Домен сайта:</td>
		<td><input type="text" name="domain" value="<?php echo SITE_DOMAIN ?>"></td>
		<td>Адрес на котором находится сайт.</td>
		</tr>
		<tr>
		<td>Каталог uCMS:</td>
		<td><input type="text" name="ucms_dir" value="<?php echo UCMS_DIR ?>"></td>
		<td>Папка, в которой находится uCMS (если есть), например: <?php echo SITE_DOMAIN; ?>/ucms - папка /ucms.</td>
		</tr>
		<tr>
		<td>Временная зона:</td>
		<td><select name="timezone" style="width: 200px;">
			<?php
				include "replaces.php";
			$strings = file("timezones.txt");
			$c = 0;
			foreach($strings as $string){
				if(preg_match("/@/", $string)){
					$string = preg_replace("/@/", "", $string);
					if($c > 0) echo "</optgroup>";
					echo "<optgroup label=\"$string\">";
				}else{
					$string = trim($string);
					echo "<option value=\"$string\" ".(UCMS_TIMEZONE === $string ? "selected" : "").">".$names[$c]."</option>";
				}
				$c++;
				if(!isset($names[$c])) echo "</optgroup>";
			}
			?>
		</select></td>
		<td>Временная зона для региона в котором вы находитесь.</td>
		</tr>
		<tr>
		<td>Ссылка на PHPMyAdmin:</td>
		<td><input type="text" name="phpmyadmin" value="<?php echo PHPMYADMIN_LINK ?>"></td>
		<td></td>
		</tr>
		<tr>
		<td>Использование изображения с кодом:</td>
		<td>
			<select name="use_captcha">
			<option value="0" <?php if(USE_CAPTCHA == 0) echo "selected"; ?>>Не использовать</option>
			<option value="1" <?php if(USE_CAPTCHA == 1) echo "selected"; ?>>При регистрации</option>
			<option value="2" <?php if(USE_CAPTCHA == 2) echo "selected"; ?>>При добавлении комментария гостем</option>
			<option value="3" <?php if(USE_CAPTCHA == 3) echo "selected"; ?>>При добавлении комментария обычным пользователем</option>
			</select>
		</td>
		<td>Использовать проверочное изображение с кодом (капча) в формах.</td>
		</tr>
		<tr>
			<td colspan="2"><input class="ucms-button-submit" type="submit" name="settings-update" value="Изменить"></td>
		</tr>
		
		</table>
		
		
	</form>

<?php } ?>
<?php include "footer.php"; ?>
