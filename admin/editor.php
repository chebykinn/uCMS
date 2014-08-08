<?php
$title = "Редактор :: ";
include 'head.php';
include 'sidebar.php';
if(!$user->has_access(5, 7)) header("Location: index.php");
?>
<div id="content">
<?php
		
	if(isset($_GET['type'])){
		switch ($_GET['type']) {
			case 'themes':
				echo "<h2>Редактор файлов тем</h2><br>";
				$fields = $udb->get_rows("SELECT `name`, `dir` FROM `".UC_PREFIX."themes` ORDER BY `id` DESC");
				$type = "themes";
				$uc_path = UC_THEMES_PATH;
				break;
			
			case 'widgets':
				echo "<h2>Редактор файлов виджетов</h2><br>";
				$fields = $udb->get_rows("SELECT `name`, `dir` FROM `".UC_PREFIX."widgets` ORDER BY `id` DESC");
				$type = "widgets";
				$uc_path = WIDGETS_PATH;
				break;
		}
	}else header("Location: ".$ucms->get_back_url());
	if(isset($_GET['action'])){
		switch ($_GET['action']) {
			case 'edit':
				if(isset($_SESSION['success'])){
					echo '<div class="success">Файл успешно изменен.</div>';
					unset($_SESSION['success']);
				}
				if(isset($_POST['file'])){
					$file = $_POST['file'];
					$link = $_POST['link'];
					$contents = $_POST['contents'];
					if(file_exists($file)){
						$edit = fopen($file, "w+");
						fprintf($edit, '%s', $contents);
						fclose($edit);
						$_SESSION['success'] = true;
						header("Location: editor.php$link");
					}
				}
				$folder = $udb->parse_value($_GET['dir']);
				load_editor($folder);
			exit;
		}
	}
	
	if($fields){
		for ($i = 0; $i < count($fields); $i++) { 
			echo "<a href=\"".UCMS_DIR."/admin/editor.php?type=$type&amp;action=edit&amp;dir=".$fields[$i]['dir']."\">".$fields[$i]['name']."</a><br>";
		}
	}
include 'footer.php';


function load_editor($folder){
	global $type, $uc_path;
	$path = $uc_path.$folder;
	if(isset($_GET['file'])){
		$efile = $_GET['file'];
	}else $efile = "index.php";
	if ( !preg_match("#(.php|.html|.htm|.txt|.css)#", $efile) or preg_match("#(../)#", $efile) or $efile == '' or !file_exists("../".$path."/".$efile) ){
		$efile = "index.php";
	}
	$contents = file_get_contents("../".$path."/".$efile);
	$link = "?type=$type&amp;action=edit&amp;dir=$folder&amp;file=$efile";
	?>
	<table style="width: 100%; border-spacing: 10px;">
		<tr>
			<td style="width: 80%" class="forms">
			<h2>Редактирование файла "<?php echo $efile;?>"</h2><br>
			<form action="editor.php<?php echo $link; ?>" method="post" >
				<input type="hidden" name="file" value="<?php echo "../".$path."/".$efile; ?>">
				<input type="hidden" name="link" value="<?php echo $link; ?>">
				<textarea style="width: 100%;  height: 600px;" name="contents" ><?php echo $contents; ?></textarea>
				<br><br>
				<input type="submit" class="ucms-button-submit" value="Изменить файл">
			</form>
			</td>
			<td>
				<h2>Файлы в папке:<br><?php echo '/'.$uc_path.$folder; ?></h2><br>
				<?php
					$dir = "../".$path."/";
					if (is_dir($dir)) {
    					if ($dh = opendir($dir)) {
    						echo "<ul class=\"files\">";
    					    while (($file = readdir($dh)) !== false) {
    					    	if($file != "." && $file != ".." && preg_match("#(.php|.html|.htm|.txt|.css)#", $file))
    					       		echo "<li><a ".($file == $efile ? 'style="color: #fff; background: #0099FF;"' : "")."href=\"?type=$type&amp;action=edit&amp;dir=$folder&amp;file=$file\">$file</a></li>";
    					    }
    					    echo "</ul>";
    					    closedir($dh);
    					}
					}
				?>
			</td>
		</tr>
	</table>
	<?php
}
?>