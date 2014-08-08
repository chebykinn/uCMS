<?php
include 'config.php';
$title = $ucms->cout("admin.editor.title", true)." :: ";
include 'head.php';
add_sidebar_item($ucms->cout("admin.editor.sidebar", true), UCMS_DIR."/admin/editor.php", "o:themes,widgets,plugins,fileman", 3, false, "");
include 'sidebar.php';
if(!$user->has_access("o:themes,widgets,plugins,fileman", 3)) header("Location: index.php");
		
	if(isset($_GET['type'])){
		switch ($_GET['type']) {
			case 'themes':
				echo "<h2>".$ucms->cout("admin.editor.header.themes", true)."</h2><br>";
				$fields = $theme->get_themes();
				$type = "themes";
				$default_file = 'themeinfo.txt';
				$uc_path = UC_THEMES_PATH;
			break;
			
			case 'widgets':
				echo "<h2>".$ucms->cout("admin.editor.header.widgets", true)."</h2><br>";
				$fields = $widget->get_widgets();
				$type = "widgets";
				$default_file = 'widgetinfo.txt';
				$uc_path = WIDGETS_PATH;
			break;

			case 'modules':
				echo "<h2>".$ucms->cout("admin.editor.header.modules", true)."</h2><br>";
				$fields = get_modules();
				$type = "modules";
				$default_file = 'moduleinfo.txt';
				$uc_path = MODULES_PATH;
			break;

			case 'plugins':
				echo "<h2>".$ucms->cout("admin.editor.header.plugins", true)."</h2><br>";
				$fields = $plugin->get_plugins();
				$type = "plugins";
				$default_file = 'plugininfo.txt';
				$uc_path = PLUGINS_PATH;
			break;

			case 'uploads':
				echo "<h2>".$ucms->cout("admin.editor.header", true)."</h2><br>";
				$type = "uploads";
				$default_file = 'index.php';
				$uc_path = UPLOADS_PATH;
			break;
		}
	}else{
		echo "<h2>".$ucms->cout("admin.editor.header", true)."</h2><br>";
		echo '<ul class="files">';
		echo '<li><a href="?type=themes">'.$ucms->cout("admin.editor.header.themes", true).'</a></li>';
		echo '<li><a href="?type=widgets">'.$ucms->cout("admin.editor.header.widgets", true).'</a></li>';
		echo '<li><a href="?type=modules">'.$ucms->cout("admin.editor.header.modules", true).'</a></li>';
		echo '<li><a href="?type=plugins">'.$ucms->cout("admin.editor.header.plugins", true).'</a></li>';
		echo "</ul>";
	}
	if(isset($_GET['action'])){
		switch ($_GET['action']) {
			case 'edit':
				if(isset($_SESSION['success'])){
					echo '<div class="success">'.$ucms->cout("admin.editor.success", true).'</div>';
					unset($_SESSION['success']);
				}
				if(isset($_POST['file'])){
					$file = $_POST['file'];
					$contents = $_POST['contents'];
					if(file_exists($file)){
						$edit = fopen($file, "w+");
						fprintf($edit, '%s', $contents);
						fclose($edit);
						$_SESSION['success'] = true;
						$event->do_actions("file.updated", array($file));
						header("Location: ".$ucms->get_back_url());
					}
				}
				$folder = isset($_GET['dir']) ? $udb->parse_value($_GET['dir']) : "";
				load_editor($folder);
			break;
		}
	}else{
		
		if(!empty($type)){
			if($type != "uploads"){
				echo "<a href=\"".UCMS_DIR."/admin/editor.php\">".$ucms->cout("admin.editor.button.back", true)."</a><br><br>";
				echo '<ul class="files">';
				for ($i = 0; $i < count($fields); $i++) { 
					switch ($type) {
						case 'themes':
							$name = $theme->get("local_name", $fields[$i]['dir']);
						break;

						case 'widgets':
							$name = $widget->get("local_name", $fields[$i]['dir']);
						break;

						case 'modules':
							$name = get_module("local_name", $fields[$i]['dir']);
						break;

						case 'plugins':
							$name = $plugin->get("local_name", $fields[$i]['dir']);
						break;
					}
					echo "<li><a href=\"".UCMS_DIR."/admin/editor.php?type=$type&amp;action=edit&amp;dir=".$fields[$i]['dir']."\">$name</a></li>";
				}
				echo "</ul>";
			}else{
				header("Location: ".UCMS_DIR."/admin/manage.php?module=fileman");
				exit;
			}
		}
	}
include 'footer.php';


function load_editor($folder){
	global $type, $uc_path, $ucms, $default_file;
	$subdir = (isset($_GET['subdir']) ? $_GET['subdir'] : "");
	if(preg_match("/(\.\.)+/", $subdir)) $subdir = '';
	$path = $uc_path.$folder.(substr($uc_path.$folder, -1) != '/' ? '/' : '').$subdir;
	$backdir = explode("/", substr($subdir, 0, strlen($subdir)-1));
	array_pop($backdir);
	$backdir = implode("/", $backdir).'/';
	if($backdir == '/') $backdir = '';
	if(isset($_GET['file'])){
		$efile = $_GET['file'];
	}else{
		$efile = $default_file;
	}
	$filetype = "";
	$newfile = false;
	if(is_array(@file("../".$path."/".$efile))){
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$filetype = finfo_file($finfo, "../".$path."/".$efile);
		finfo_close($finfo);
	}
	if ( !preg_match('#('.EDITABLE_FILETYPES.')#', $filetype) or preg_match("#(../)#", $efile) or $efile == '' or !is_array(@file("../".$path."/".$efile))){
		if(is_array(@file("../".$path."/index.php"))){
			$efile = "index.php";
		}else{
			$newfile = true;
			$efile = $ucms->cout("admin.editor.new_file.name", true);
			// header("Location: ".UCMS_DIR."/admin/manage.php?module=fileman");
			// return false;
		}
	}
	if(!$newfile){
		$contents = file_get_contents("../".$path."/".$efile);
	}else{
		$contents = "";
	}
	$link = "?type=$type&amp;action=edit&amp;dir=$folder&amp;file=$efile";
	echo "<a href=\"".UCMS_DIR."/admin/editor.php?type=$type\">".$ucms->cout("admin.editor.button.back", true)."</a><br><br>";
	?>
	<table style="width: 100%; border-spacing: 10px;">
		<tr>
			<td class="forms">
			<h2><?php $ucms->cout("admin.editor.file_editing", false, $efile); ?></h2><br>
			<form action="editor.php<?php echo $link; ?>" method="post" >
				<input type="hidden" name="file" value="<?php echo "../".$path."/".$efile; ?>">
				<textarea class="file-editor" name="contents" ><?php echo htmlspecialchars($contents); ?></textarea>
				<br><br>
				<input type="submit" class="ucms-button-submit" value="<?php $ucms->cout("admin.editor.button.edit_file"); ?>">
			</form>
			</td>
			<td style="width: 200px">
				<h2><?php $ucms->cout("admin.editor.files_in_folder"); ?><br><?php echo '/'.$path; ?></h2><br>
				<?php
					$dir = ABSPATH.$path."/";
					if (is_dir($dir)) {
						if ($dh = opendir($dir)) {
    						echo "<ul class=\"files\">";
    						if($subdir != '' and $subdir != '/'){
								echo "<li><a href=\"?type=$type&amp;action=edit&amp;dir=$folder&amp;subdir=$backdir\">".$ucms->cout("admin.editor.button.back", true)."</a></li>";
    						}
    						echo "<li><br></li>";
    						$finfo = finfo_open(FILEINFO_MIME_TYPE);
    					    while (($directory = readdir($dh)) !== false) {
    					    	$filetype = finfo_file($finfo, $dir.$directory);
    					    	if($directory != "." && $directory != ".." && $filetype == 'directory'){
    					       		echo "<li><a href=\"?type=$type&amp;action=edit&amp;dir=$folder&amp;subdir=$subdir$directory/\">$directory</a></li>";
    					    	}
    					    }
    						echo "<li><br></li>";
    						$dh = opendir($dir);
    					    while (($file = readdir($dh)) !== false) {
    					    	$filetype = finfo_file($finfo, $dir.$file);
    					    	if($file != "." && $file != ".." && preg_match('#('.EDITABLE_FILETYPES.')#', $filetype)){
    					       		echo "<li><a ".($file == $efile ? 'style="color: #fff; background: #0099FF;"' : "")."href=\"?type=$type&amp;action=edit&amp;dir=$folder&amp;file=$file&amp;subdir=$subdir\">$file</a></li>";
    					    	}
    					    }
    					    echo "</ul>";
    					    closedir($dh);
    					    finfo_close($finfo);
    					}
					}
				?>
			</td>
		</tr>
	</table>
	<?php
}
?>