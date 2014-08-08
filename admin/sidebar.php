<td id="sidebar-cell">
<div class="sidebar">
<?php $event->do_actions("admin.sidebar.top"); ?>
	<ul>
		<?php
			function sidebar_sort($a, $b){
				if ($a["order"] == $b["order"]) {
					return 0;
				}
				return ($a["order"] < $b["order"]) ? -1 : 1;
			}

			add_sidebar_item("admin.sidebar.main", UCMS_DIR."/admin/index.php", "at_least_one", 1, false, 0);
			if(MODULES_ENABLED)
				add_sidebar_item("admin.sidebar.modules", UCMS_DIR."/admin/modules.php", "system", 7, false, 11);

			add_sidebar_item("admin.sidebar.settings", UCMS_DIR."/admin/settings.php", "system", 7, true, count($links)+count($settings_links)+100, '?module=general');
			usort($settings_links, "sidebar_sort");
			for ($i = 0; $i < count($settings_links); $i++) {
				if(isset($settings_links[$i]['name']) and isset($settings_links[$i]['file'])){
					add_sidebar_item($settings_links[$i]['name'], $settings_links[$i]['file'], $settings_links[$i]['accessID'], $settings_links[$i]['accessLVL'], false, count($links)+count($settings_links)+$i+100);
				}
			}
			add_sidebar_item("admin.sidebar.updates", UCMS_DIR."/admin/update.php", "system", 7, true, count($links)+1000);
			usort($links, "sidebar_sort");
			$c = 0;
			if($user->has_access("system") and PHPMYADMIN_LINK != ''){ 
				add_sidebar_item("admin.sidebar.phpmyadmin", PHPMYADMIN_LINK, "system", 7, true, count($links)+1000);
			}
			for($i = 0; $i < count($links); $i++){
				$light_top = false;
				if($user->has_access($links[$i]['accessID'], $links[$i]['accessLVL']) and $links[$i]['name'] != ""){
					if($links[$i]['delimeter']){
						echo '<li class="sidebar-border"></li>';
					}
						$url = isset($_GET['module']) ? $_SERVER['PHP_SELF'].'?module='.$_GET['module'] : $_SERVER['PHP_SELF'];
							if($url == $links[$i]['file']){
								echo '<li class="selected">';
							}
							else{

								$url2 = isset($_GET['module']) ? $_SERVER['PHP_SELF'].'?module='.$_GET['module'] : $_SERVER['PHP_SELF'];
								if($url2 == $links[$i]['file'] and preg_match("/@/", $links[$i+1]['name'])){
									echo '<li class="selected">';
								}else
									if(!preg_match("#(manage.php)#", $links[$i]['file']) and preg_match("#(".$_SERVER['PHP_SELF'].")#", $links[$i]['file']) and preg_match("/@/", $links[$i+1]['name']))
										echo '<li class="selected">';
									else
										echo "<li>";
							}
						if(preg_match("/@/", $links[$i]['name'])){
							$url3 = (isset($_GET['module']) and isset($_GET['section'])) ? $_SERVER['PHP_SELF'].'?module='.$_GET['module'].'&amp;section='.$_GET['section'] : $_SERVER['PHP_SELF'];
							echo '<ul>';
							echo '<li '.(($url == $links[$i]['file'] or $url3 == $links[$i]['file']) ? 'class="selected2"' : "").'><a href="'.$links[$i]['file'].'">'.substr($links[$i]['name'], 1).'</a></li>';
							echo '</ul>';
						}else
							echo '<a href="'.$links[$i]['file'].'">'.$links[$i]['name'].'</a>';
						echo "</li>";
				}else{
					
				}
			}
		 ?>
		<li style="margin-top: 20px;"><a href="<?=SITE_DOMAIN.UCMS_DIR?>" ><?php $ucms->cout("admin.sidebar.sitelink") ?></a></li>
	</ul>
	<?php $event->do_actions("admin.sidebar.bottom"); ?>
</div>
</td>
<td id="content-cell">
<div id="content">
<?php
if(!empty($_SESSION['ucms-update-available'])){
	if(!$user->has_access("system")){
		echo "<div class=\"warning\"><b>".$ucms->cout("admin.alert.warning.update_available", true, $_SESSION['ucms-update-available'])."</b></div><br>";
	}
	else{
		echo "<div class=\"warning\"><b><a href=\"update.php\">".$ucms->cout("admin.alert.warning.update_available", true, $_SESSION['ucms-update-available'])."</a></b></div><br>";
	}
}
$event->do_actions("admin.content.top"); 
?>