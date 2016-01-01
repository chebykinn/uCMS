<?php

function dir_and_files_sort($a, $b){
	return ($a['type'] == 'directory') ? -1 : 1;
}

function sort_by_size_asc($a, $b){
	if ($a['size'] == $b['size']) {
		return 0;
	}
	return ($a['size'] < $b['size']) ? -1 : 1;
}

function sort_by_size_desc($a, $b){
	if ($a['size'] == $b['size']) {
		return 0;
	}
	return ($a['size'] > $b['size']) ? -1 : 1;
}

function sort_by_type_asc($a, $b){
	if ($a['type'] == $b['type']) {
		return 0;
	}
	return ($a['type'] < $b['type']) ? -1 : 1;
}

function sort_by_type_desc($a, $b){
	if ($a['type'] == $b['type']) {
		return 0;
	}
	return ($a['type'] > $b['type']) ? -1 : 1;
}

function add_file($p){
	global $user;
	if($user->has_access("fileman", 2)){
		$files = $_FILES['file'];
		$backdir = (!empty($_POST['backdir']) and is_dir(ABSPATH.UPLOADS_PATH.$_POST['backdir'])) ? $_POST['backdir'] : "";
		for($i = 0; $i < count($files['name']); $i++){
			$files['name'][$i] = strtolower(preg_replace('/\s/', "_", $files['name'][$i]));
			$files['name'][$i] = strtolower(preg_replace(URL_REGEXP, "", $files['name'][$i]));
		
			move_uploaded_file($files['tmp_name'][$i], ABSPATH.UPLOADS_PATH.$backdir.$files['name'][$i]);
		}
		if($i > 1)
			header("Location: manage.php?module=fileman&dir=$backdir&alert=added_multiple");
		else
			header("Location: manage.php?module=fileman&dir=$backdir&alert=added");
		return true;
	}
	header("Location: manage.php?module=fileman");
	return false;
}

function create_directory(){
	if(isset($_GET['newdir']) and $_GET['newdir'] != ''){
		if(isset($_GET['backdir'])){
			$backdir = $_GET['backdir'];
		}else{
			$backdir = "";
		}
		$cdir = ABSPATH.UPLOADS_PATH.$backdir.$_GET['newdir'];
		$ddir = $backdir.$_GET['newdir'];
		if(!is_dir($cdir))
			mkdir($cdir);
		header("Location: manage.php?module=fileman&dir=$backdir&alert=created_dir");
		return true;
	}
	header("Location: manage.php?module=fileman");
}

function download_file($filename) {
	if (!file_exists($filename)) {
		header("Location: ".UCMS_DIR."/admin/manage.php?module=fileman");
		exit;
	} else {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mimetype = finfo_file($finfo, $filename);
		finfo_close($finfo);
		$fn = basename($filename);
		$fn = preg_replace("/\s/", "_", $fn);
		$fsize = filesize($filename);
		$ftime = date("D, d M Y H:i:s T", filemtime($filename));
		$fd = fopen($filename, "rb");
		if (!$fd){
			header ("HTTP/1.0 403 Forbidden");
			exit;
		}
		if (isset($_SERVER["HTTP_RANGE"])){
			$range = $_SERVER["HTTP_RANGE"];
			$range = str_replace("bytes=", "", $range);
			$range = str_replace("-", "", $range);
			if ($range)
			fseek($fd, $range);
		}else $range = 0;

		if($range > 0){
			header("HTTP/1.1 206 Partial Content");
		}else{
			header("HTTP/1.1 200 OK");
		}
		header("Content-Disposition: attachment; filename=$fn");
		header("Last-Modified: $ftime");
		header("Accept-Ranges: bytes");
		header("Content-Length: ".($fsize-$range));
		header("Content-Range: bytes $range-".($fsize -1)."/".$fsize);
		header("Content-type: $mimetype");

		fpassthru($fd);
		exit;
	}
} 

function list_images(){
	global $ucms;
	$default = ABSPATH.UPLOADS_PATH;
	if(!isset($_POST['dir']) || $_POST['dir'] == "" || preg_match("/(\.\.\/)+/", $_POST['dir']) ){
		$dir = $default;
		$dir_url = "";
		$subdir = false;
	}else{
		$dir = $default.$_POST['dir'].(substr($_POST['dir'], -1) != '/' ? '/' : "");
		$dir_url = $_POST['dir'].(substr($_POST['dir'], -1) != '/' ? '/' : "");
		$subdir = true;
		$path = explode("/", substr($dir_url, 0, strlen($dir_url)-1));
		array_pop($path);
		$backdir = implode("/", $path);
	}
	if (is_dir($dir)) {
		if ($dh = @opendir($dir)) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$f = 0;
			while (($file = readdir($dh)) !== false) {
				if($file !== '.' && $file !== '..'){
					$files[$f]['name'] = $file;
					$files[$f]['type'] = finfo_file($finfo, $dir.$file);
					$files[$f]['size'] = filesize($dir . $file);
					$files[$f]['date'] = filemtime($dir . $file);
					$f++;
				}
			}
			finfo_close($finfo);
			closedir($dh);
		}
	}
	?>
	<table class="manage">
		<tr>
			<th><?php $ucms->cout("module.fileman.table.header.preview"); ?></th>
			<th><?php $ucms->cout("module.fileman.table.header.name"); ?></th>
			<th><?php $ucms->cout("module.fileman.table.header.size"); ?></th>
			<th></th>
		</tr>
		
		<?php
	$ext = array(
		0 => $ucms->cout("module.fileman.size.bin_unit.b", true),
		1 => $ucms->cout("module.fileman.size.bin_unit.kib", true),
		2 => $ucms->cout("module.fileman.size.bin_unit.mib", true),
		3 => $ucms->cout("module.fileman.size.bin_unit.gib", true),
		4 => $ucms->cout("module.fileman.size.bin_unit.tib", true),
		5 => $ucms->cout("module.fileman.size.bin_unit.pib", true),
		6 => $ucms->cout("module.fileman.size.bin_unit.eib", true),
		7 => $ucms->cout("module.fileman.size.bin_unit.zib", true),
		8 => $ucms->cout("module.fileman.size.bin_unit.yib", true));
	if($subdir)
		echo '<tr><td colspan="5"><a href="'.$backdir.'" class="go-back">'.$ucms->cout("module.fileman.table.up.button", true).'</a></td></tr>';
	if (isset($files)) {
		usort($files, 'sort_by_name_asc');
			for ($i = 0; $i < count($files); $i++) { 
				if($files[$i]['type'] == 'directory'){
					echo '<tr><td colspan="4"><a class="change_dir" href="'.$dir_url.$files[$i]['name'].'">'.$files[$i]['name'].'</a></td>';
				}else $isimage = false;	
			}
			for ($i = 0; $i < count($files); $i++) { 
				$fsize = $files[$i]['size'];
				$c = 0;
				while($fsize >= 1024){
					$fsize /= 1024;
					$fsize = round($fsize, 1);
					$c++;
				}

				if(preg_match('#(image/png|image/gif|image/jpeg)#', $files[$i]['type'])){
					$url = SITE_DOMAIN.UCMS_DIR.'/'.UPLOADS_PATH.$dir_url.$files[$i]['name'];
					echo '<tr><td><a target="_blank" href="'.$url.'"><img class="preview" src="'.$url.'" alt="" width="64" height="64"></a></td>';
					echo '<td>'.$files[$i]['name'].'</td>';
					echo '<td>'.$fsize.' '.(isset($ext[$c]) ? "<b>$ext[$c]</b>" : $ucms->cout("module.fileman.size.bin_unit.unknown", true)).'</td>';
					echo '<td><br><a class="ucms-add-button image" href="'.$url.'">'.$ucms->cout("module.fileman.table.select_image.button", true).'</a></td>';
				}else $isimage = false;

				
			}
			
	}else{
		echo "<tr><td colspan=\"5\" style=\"text-align:center;\">".$ucms->cout("module.fileman.table.empty.label", true)."</td></tr>";
	}
		?>
	</table></form>
	<?php
}

function manage_files(){
	global $ucms, $user;
	$default = ABSPATH.UPLOADS_PATH;
	$dir = $default;
	$dir_url = "";
	$move = false;
	$subdir = false;
	$ddir = !empty($_GET['dir']) ? $_GET['dir'] : "";
	if(!is_dir($default.$ddir) || preg_match("/(\.\.\/)+/", $ddir) ){
		header("Location: manage.php?module=fileman");
		exit;
	}elseif($ddir != ''){
		$dir = $default.$ddir.(substr($ddir, -1) != '/' ? '/' : "");
		$dir_url = $ddir.(substr($ddir, -1) != '/' ? '/' : "");
		$subdir = true;
		$path = explode("/", substr($dir_url, 0, strlen($dir_url)-1));
		array_pop($path);
		$backdir = implode("/", $path);
	}

	if (isset($_POST['item']) and isset($_POST['actions']) and !isset($_POST['rename']) and !isset($_POST['paste']) and !isset($_POST['cancel-paste']) ){
		$items = array();
		$action = (int) $_POST['actions'];
		foreach ($_POST['item'] as $id) {
			$items[] = $id;
		}
		$ids = implode(',', $items);
		if (count($items) > 0) {
			switch ($action) {
				case 1:
					$_SESSION['cut_files'] = $items;
					$_SESSION['files_dir'] = $dir;
					header("Location: manage.php?module=fileman&dir=$dir_url");
				break;
				
				case 2:
					$_SESSION['copy_files'] = $items;
					$_SESSION['files_dir'] = $dir;
					header("Location: manage.php?module=fileman&dir=$dir_url");
				break;

				case 3:
					if($user->has_access("fileman", 4)){
						foreach ($items as $item) {
							$item = explode("|", $item);
							if($item[0] == 'dir'){
								$ucms->remove_dir($dir.$item[1]);
							}else{	
								unlink($dir.$item[1]);
							}
						}
						header("Location: manage.php?module=fileman&dir=$dir_url&alert=deleted_multiple");
					}
				break;
				
			}
		}
	}
	if( isset($_POST['cancel-paste']) and (!empty($_SESSION['copy_files']) or !empty($_SESSION['cut_files'])) and !empty($_SESSION['files_dir']) ){
		$move_type = !empty($_SESSION['copy_files']) ? 'copy_files' : 'cut_files';
		unset($_SESSION[$move_type]);
		unset($_SESSION['files_dir']);
		header("Location: manage.php?module=fileman&dir=$dir_url");
		exit;
	}


	if( isset($_POST['paste']) and (!empty($_SESSION['copy_files']) or !empty($_SESSION['cut_files'])) and !empty($_SESSION['files_dir']) ){
		$selected_files = !empty($_SESSION['copy_files']) ? $_SESSION['copy_files'] : $_SESSION['cut_files'];
		$move_type = !empty($_SESSION['copy_files']) ? 'copy_files' : 'cut_files';
		$alert = !empty($_SESSION['copy_files']) ? 'copied' : 'moved';
		if($_SESSION['files_dir'] == $dir){
			header("Location: manage.php?module=fileman&dir=$dir_url&alert=same_dir");
			exit;
		}
		foreach ($selected_files as $file) {
			$file = explode("|", $file);
			if(file_exists($_SESSION['files_dir'].$file[1]) or is_dir($_SESSION['files_dir'].$file[1])){
				if($move_type == 'cut_files'){
					$done = rename($_SESSION['files_dir'].$file[1], $dir.$file[1]);
				}
				else{
					if($file[0] == 'dir'){
						if(substr($dir, 0, strlen($dir)-1) != $_SESSION['files_dir'].$file[1]){
							copy_dir($_SESSION['files_dir'].$file[1], $dir.$file[1]);
							$done = true;
						}else{
							$done = false;
						}
					}else
						$done = copy($_SESSION['files_dir'].$file[1], $dir.$file[1]);
					
				}
				if(!$done){
					header("Location: manage.php?module=fileman&dir=$dir_url&alert=paste_error");
					exit;
				}
			}
		}
		unset($_SESSION['files_dir']);
		unset($_SESSION[$move_type]);
		header("Location: manage.php?module=fileman&dir=$dir_url&alert=$alert");
		exit;
	}

	if( !empty($_POST['filename']) and !empty($_POST['dir']) 
		and !empty($_POST['oldname']) and (file_exists($_POST['dir'].$_POST['oldname']) or is_dir($_POST['dir'].$_POST['oldname'])) ){

		$_POST['filename'] = strtolower(preg_replace('/\s/', "_", $_POST['filename']));
		$_POST['filename'] = strtolower(preg_replace(URL_REGEXP, "", $_POST['filename']));
		rename($_POST['dir'].$_POST['oldname'], $_POST['dir'].$_POST['filename']);
		header("Location: manage.php?module=fileman&dir=$dir_url&alert=renamed");
		exit;
	}
	echo "<br><h3>".$ucms->cout("module.fileman.current_dir.label", true).UPLOADS_PATH."$dir_url</h3><br>";
	$order = (isset($_GET['order']) and $_GET['order'] == 'desc') ? 'asc' : 'desc';
	$link1 = UCMS_DIR."/admin/manage.php?module=fileman&amp;".(isset($_GET['dir']) ? "dir=".$_GET['dir']."&amp;" : "")."orderby=name&amp;order=".$order;
	$link2 = UCMS_DIR."/admin/manage.php?module=fileman&amp;".(isset($_GET['dir']) ? "dir=".$_GET['dir']."&amp;" : "")."orderby=type&amp;order=".$order;
	$link3 = UCMS_DIR."/admin/manage.php?module=fileman&amp;".(isset($_GET['dir']) ? "dir=".$_GET['dir']."&amp;" : "")."orderby=size&amp;order=".$order;
	$link4 = UCMS_DIR."/admin/manage.php?module=fileman&amp;".(isset($_GET['dir']) ? "dir=".$_GET['dir']."&amp;" : "")."orderby=date&amp;order=".$order;
	$mark = $order == "asc" ? '↑' : '↓';
	?>
	<form action="manage.php?module=fileman<?php echo '&amp;dir='.$dir_url; ?>" method="post">
	<?php if($user->has_access("fileman", 3)){ ?>
	<select name="actions" style="width: 250px;">
		<option><?php $ucms->cout("module.fileman.selected.label"); ?></option>
		<option value="1"><?php $ucms->cout("module.fileman.selected.cut.label"); ?></option>
		<option value="2"><?php $ucms->cout("module.fileman.selected.copy.label"); ?></option>
		<?php if($user->has_access("fileman", 4)){ ?><option value="3"><?php $ucms->cout("module.fileman.selected.delete.label"); ?></option><?php } ?>
	</select><input type="submit" value="<?php $ucms->cout("module.fileman.selected.apply.button"); ?>" class="ucms-button-submit">
	<?php
		if(!empty($_SESSION['copy_files']) or !empty($_SESSION['cut_files'])){
			$move = true;
			$move_type = !empty($_SESSION['copy_files']) ? $ucms->cout("module.fileman.paste.type.copy", true) : $ucms->cout("module.fileman.paste.type.cut", true);
			$selected_files = !empty($_SESSION['copy_files']) ? $_SESSION['copy_files'] : $_SESSION['cut_files'];
			$count = count($selected_files);
			echo '<b>'.$ucms->cout("module.fileman.paste.label", true, $move_type, $count).'</b> 
			<input type="submit" name="paste" value="'.$ucms->cout("module.fileman.paste.button", true).'" class="ucms-button-submit">
			<input type="submit" name="cancel-paste" value="'.$ucms->cout("module.fileman.cancel-paste.button", true).'" class="ucms-button-submit">';
		}
	} 

	if (is_dir($dir)) {
		if ($dh = @opendir($dir)) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$f = 0;
			$total_size = 0;
			while (($file = readdir($dh)) !== false) {
				if($file !== '.' && $file !== '..'){
					$files[$f]['name'] = $file;
					$files[$f]['type'] = finfo_file($finfo, $dir.$file);
					$files[$f]['size'] = filesize($dir . $file);
					$files[$f]['timestamp'] = filemtime($dir . $file);
					$total_size += $files[$f]['size'];
					$f++;
				}
			}
			finfo_close($finfo);
			closedir($dh);
		}
	}
	if(!empty($files)){
		$c_all = count($files);
		$total_size = format_filesize($total_size);
	}
	else{
		$c_all = 0;
		$total_size = format_filesize(0);
	}
	?><br><br>
	<?php $ucms->cout("module.fileman.total.label", false, $c_all, $total_size); ?><br><br>
	<table class="manage">
		<tr>
			<th style="width: 15px;"><input type="checkbox" name="select-all" value="1"></th>
			<th style="width: 50%"><a href="<?php echo $link1; ?>"><?php $ucms->cout("module.fileman.table.header.name"); echo $mark; ?></a></th>
			<th><a href="<?php echo $link2; ?>"><?php $ucms->cout("module.fileman.table.header.type"); echo $mark; ?></th>
			<th style="width: 150px;"><a href="<?php echo $link3; ?>"><?php $ucms->cout("module.fileman.table.header.size"); echo $mark; ?></a></th>
			<th style="width: 150px;"><a href="<?php echo $link4; ?>"><?php $ucms->cout("module.fileman.table.header.date"); echo $mark; ?></a></th>
			<th style="width: 150px;"><?php $ucms->cout("module.fileman.table.header.manage"); ?></th>
		</tr>
		
		<?php
			
			
			if($subdir)
				echo '<tr><td colspan="6"><a href="?module=fileman&dir='.$backdir.'">'.$ucms->cout("module.fileman.table.up.button", true).'</a></td></tr>';
			if (isset($files)) {
				if(isset($_GET['orderby'])){
					if(function_exists("sort_by_".$_GET['orderby'].'_'.$order))
					usort($files, "sort_by_".$_GET['orderby'].'_'.$order);
				}else
					usort($files, 'dir_and_files_sort');
					for ($i = 0; $i < count($files); $i++) { 
						if($files[$i]['type'] != 'directory'){
							if(preg_match('#(image/png|image/gif|image/jpeg)#', $files[$i]['type'])){
								$isimage = true;
							}else $isimage = false;

							if(preg_match('#('.EDITABLE_FILETYPES.')#', $files[$i]['type'])){
								$edit = true;
							}else $edit = false;

							if(preg_match('#(application/zip)#', $files[$i]['type'])){
								$archive = true;

							}else $archive = false;
							$download_link = NICE_LINKS ? UCMS_DIR.'/download/'.$dir_url.$files[$i]['name'] : UCMS_DIR.'/?action=download&amp;file='.$dir_url.$files[$i]['name'];
							echo '<tr><td><input type="checkbox" name="item[]" value="file|'.$files[$i]['name'].'"></td>';
							echo '<td>'.( (isset($_GET['action']) and $_GET['action'] == 'rename' and isset($_GET['file']) and $_GET['file'] == $files[$i]['name'] )
								? '<input style="width: 200px;" type="text" name="filename" value="'.htmlspecialchars($files[$i]['name']).'">
									<input type="hidden" name="oldname" value="'.$files[$i]['name'].'">
									<input type="hidden" name="dir" value="'.$dir.'">
									<input type="submit" name="rename" value="OK" class="ucms-button-submit">' : 
									(($move and $_SESSION['files_dir'] == $dir and in_array('file|'.$files[$i]['name'], $selected_files)) ? '<b>'.$files[$i]['name']."</b>"
									 : $files[$i]['name'])).'
								 <span style="float:right;">
								 '.($user->has_access("fileman", 2) ? '<a href="'.$download_link.'">'.$ucms->cout("module.fileman.table.download.link", true).'</a>' : "")
								.(($archive and $user->has_access("fileman", 3)) ? '
									<a href="manage.php?module=fileman&amp;action=extract&amp;extractto='.$dir_url.'&amp;file='.$files[$i]['name'].'">'.$ucms->cout("module.fileman.table.extract.link", true).'</a>' : "")
								.(($isimage  and $user->has_access("fileman", 1)) ? '
									<a target="_blank" href="'.SITE_DOMAIN.UCMS_DIR.'/'.UPLOADS_PATH.$dir_url.$files[$i]['name'].'">'.$ucms->cout("module.fileman.table.view_image.link", true).'</a>' : "")
								.(($edit and $user->has_access("fileman", 3)) ? '
									<a href="editor.php?type=uploads&action=edit&dir='.$dir_url.'&file='.$files[$i]['name'].'">'.$ucms->cout("module.fileman.table.edit.link", true).'</a>' : "").'</span></td>';
							echo '<td>'.$files[$i]['type'].'</td>';
							echo '<td>'.format_filesize($files[$i]['size']).'</td>';
						}else{
							echo '<tr><td><input type="checkbox" name="item[]" value="dir|'.$files[$i]['name'].'"></td>';
							echo '<td>'.( (isset($_GET['action']) and $_GET['action'] == 'rename' and isset($_GET['directory']) and $_GET['directory'] == $files[$i]['name'] )
									? '<input style="width: 200px;" type="text" name="filename" value="'.htmlspecialchars($files[$i]['name']).'">
									<input type="hidden" name="oldname" value="'.$files[$i]['name'].'">
									<input type="hidden" name="dir" value="'.$dir.'">
									<input type="submit" name="rename" value="OK" class="ucms-button-submit">' : 
									(($move and $_SESSION['files_dir'] == $dir and in_array('dir|'.$files[$i]['name'], $selected_files)) ? '<b>'.$files[$i]['name']."</b>"
									 : '<a href="?module=fileman&dir='.$dir_url.$files[$i]['name'].'/">'.$files[$i]['name'].'</a></td>'));
							echo '<td colspan="2">'.$ucms->cout('module.fileman.table.directory.label', true).'</td>';
						}
						$id = $files[$i]['type'] != 'directory' ? 'file' : 'directory';
						echo "<td>".$ucms->date_format($files[$i]['timestamp'])."</td>";
						echo '<td>';
						if($user->has_access("fileman", 3)){
							echo '<span class="actions">';
							echo '<a href="?module=fileman&amp;action=rename&amp;dir='.$dir_url.'&amp;'.$id.'='.$files[$i]['name'].'">'.$ucms->cout("module.fileman.table.manage.rename.button", true).'</a>&nbsp;|&nbsp;'.($user->has_access("fileman", 4) ? '<a href="?module=fileman&amp;action=delete&dir='.$dir_url.'&amp;'.$id.'='.$files[$i]['name'].'">'.$ucms->cout("module.fileman.table.manage.delete.button", true).'</a>' : "");
							echo '</span>';
						}
						echo '</td></tr>';
					}
			}else{
				echo "<tr><td colspan=\"6\" style=\"text-align:center;\">".$ucms->cout("module.fileman.table.empty.label", true)."</td></tr>";
			}
		?>
	</table></form>
	<?php
}

function extract_archive(){
	if(isset($_GET['extractto']) and isset($_GET['file']) and !preg_match("/(\.\.\/)+/", $_GET['extractto']) ){
		$dir = ABSPATH.UPLOADS_PATH.$_GET['extractto'];
		$dir_url = $_GET['extractto'];
		$file = $_GET['file'];
		if(!empty($file)){
			$zip = new ZipArchive();
			$res = $zip->open($dir.$file);
			if($res === TRUE){
				$name = preg_replace('#(.zip)#', '', $file);
				$zip->extractTo($dir.$name);
				$zip->close();
				header("Location: manage.php?module=fileman&dir=$dir_url&alert=extracted");
				return true;
			}else{
				header("Location: manage.php?module=fileman&dir=$dir_url&alert=extract_failed");
				return false;
			}
		}
	}
	header("Location: manage.php?module=fileman");
	return false;
}

function copy_dir($source, $dest){
	if(!is_dir($dest))
		mkdir($dest);
	if($handle = opendir($source)){   
		while(false !== ($file = readdir($handle))){
			if($file != '.' && $file != '..'){
				$path = $source.'/'.$file;
				if(is_file($path)){
					if(!is_file($dest.'/'.$file))
						@copy($path, $dest.'/'.$file);
					
				} 
				elseif(is_dir($path)){
					copy_dir($path, $dest.'/'.$file);
				}
			}
		}
		closedir($handle);
	}
}

function format_filesize($size){
	global $ucms;
	$ext = array(
		0 => $ucms->cout("module.fileman.size.bin_unit.b", true),
		1 => $ucms->cout("module.fileman.size.bin_unit.kib", true),
		2 => $ucms->cout("module.fileman.size.bin_unit.mib", true),
		3 => $ucms->cout("module.fileman.size.bin_unit.gib", true),
		4 => $ucms->cout("module.fileman.size.bin_unit.tib", true),
		5 => $ucms->cout("module.fileman.size.bin_unit.pib", true),
		6 => $ucms->cout("module.fileman.size.bin_unit.eib", true),
		7 => $ucms->cout("module.fileman.size.bin_unit.zib", true),
		8 => $ucms->cout("module.fileman.size.bin_unit.yib", true));
	$c = 0;
	while($size >= 1024){
		$size /= 1024;
		$size = round($size, 1);
		$c++;
	}
	return $size." ".(isset($ext[$c]) ? "<b>$ext[$c]</b>" : $ucms->cout("module.fileman.size.bin_unit.unknown", true));
}

?>