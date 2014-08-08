<?php
/**
 *
 * uCMS Text Editor
 * @package uCMS
 * @since uCMS 1.3
 * @version uCMS 1.3
 *
*/
class Editor{
	
	/**
	 *
	 * Load main iframe textarea and scripts
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function main(){
		global $ucms;
		?>
		<iframe autofocus scrolling="yes" frameborder="no" src="#" id="editor" name="editor" width="100%" height="600"><?php $ucms->cout("admin.text-editor.error.iframe"); ?></iframe>
		<textarea autofocus tabindex="3" style="display: none;" name="html-code" id="html-code" rows="30" cols="500"></textarea>
		<script type="text/javascript" src="<?php echo UCMS_DIR; ?>/admin/scripts/editor.js"></script>
		<?php
	}

	/**
	 *
	 * Load controls
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function controls(){
		global $ucms;
		$link = UCMS_DIR.'/admin/images/icons/';
		$controls = array(
			"undo" 			=> "setFormat('undo')",
			"redo" 			=> "setFormat('redo')",
			"cut" 			=> "setFormat('cut')",
			"copy" 			=> "setFormat('copy')",
			"paste" 		=> "setFormat('paste')",
			"bold" 			=> "setFormat('bold')",
			"italic" 		=> "setFormat('italic')",
			"underline" 	=> "setFormat('underline')",
			"hyperlink" 	=> "chooseLink();",
			"image" 		=> "chooseImage();",
			"justifyleft" 	=> "setFormat('JustifyLeft')",
			"justifycenter" => "setFormat('JustifyCenter')",
			"justifyright" 	=> "setFormat('JustifyRight')",
			"dottedlist" 	=> "setFormat('insertunorderedlist')",
			"numberedlist" 	=> "setFormat('insertorderedlist')",
			"quote" 		=> "setFormat('formatBlock', 'blockquote')",
			"indent" 		=> "setFormat('indent')",
			"outdent" 		=> "setFormat('outdent')",
			"clean" 		=> "setFormat('RemoveFormat')",
			"more" 			=> "setFormat('insertText', '@-more-@".$ucms->cout("admin.text-editor.read_more.label", true)."-@')",
			"html" 			=> "ShowHTML()",
			"visual" 		=> "ShowVisualEditor()");

		?>
		<div id="editor-image">
		<div id="images">
		<div class="splash-top"><img src="images/close.png" alt="" id="close-splash"><?php $ucms->cout("admin.text-editor.add_image.header"); ?></div>
		<br><br>
		<a href="/update-images" class="update-images"><?php $ucms->cout("admin.text-editor.update-images.button"); ?></a>
		<br><br>
		<?php include ABSPATH.MODULES_PATH."fileman/manage-files.php"; 
		list_images();
		?>
		<br>
		<a href="/update-images" class="update-images"><?php $ucms->cout("admin.text-editor.update-images.button"); ?></a>
		<br><br>
		</div>
		</div>

		<div id="make-link">
			<div class="splash-top"><img src="images/close.png" alt="" id="close-splash"><?php $ucms->cout("admin.text-editor.add_link.header"); ?></div>
			<br><br><label for="url"><b><?php $ucms->cout("admin.text-editor.add_link.address"); ?></b></label><br>
			<input type="text" name="url" id="url"> 
			<br><br><a href="/add" class="ucms-add-button link" ><?php $ucms->cout("admin.text-editor.add_link.add.button"); ?></a>
			
		</div>
		<div id="shadow"></div>
		<select class="edits" style="width: 150px;" id="formatBlock">
			<option selected><?php $ucms->cout("admin.text-editor.title"); ?></option>
			<option value="h1"><?php $ucms->cout("admin.text-editor.title.h1"); ?></option>
			<option value="h2"><?php $ucms->cout("admin.text-editor.title.h2"); ?></option>
			<option value="h3"><?php $ucms->cout("admin.text-editor.title.h3"); ?></option>
			<option value="h4"><?php $ucms->cout("admin.text-editor.title.h4"); ?></option>
			<option value="h5"><?php $ucms->cout("admin.text-editor.title.h5"); ?></option>
			<option value="h6"><?php $ucms->cout("admin.text-editor.title.h6"); ?></option>
		</select>
		<select class="edits" style="width: 150px;" id="fontsize">
			<option selected><?php $ucms->cout("admin.text-editor.fontsize"); ?></option>
			<option value="1"><?php $ucms->cout("admin.text-editor.fontsize.one"); ?></option>
			<option value="2"><?php $ucms->cout("admin.text-editor.fontsize.two"); ?></option>
			<option value="3"><?php $ucms->cout("admin.text-editor.fontsize.three"); ?></option>
			<option value="4"><?php $ucms->cout("admin.text-editor.fontsize.four"); ?></option>
			<option value="5"><?php $ucms->cout("admin.text-editor.fontsize.five"); ?></option>
			<option value="6"><?php $ucms->cout("admin.text-editor.fontsize.six"); ?></option>
			<option value="7"><?php $ucms->cout("admin.text-editor.fontsize.seven"); ?></option>
		</select>
		<select class="edits" style="width: 150px;" id="forecolor">
			<option selected><?php $ucms->cout("admin.text-editor.forecolor"); ?></option>
			<option value="ff0000"><?php $ucms->cout("admin.text-editor.forecolor.red"); ?></option>
			<option value="0000ff"><?php $ucms->cout("admin.text-editor.forecolor.blue"); ?></option>
			<option value="00ff00"><?php $ucms->cout("admin.text-editor.forecolor.green"); ?></option>
			<option value="ffffff"><?php $ucms->cout("admin.text-editor.forecolor.white"); ?></option>
			<option value="000000"><?php $ucms->cout("admin.text-editor.forecolor.black"); ?></option>
			<option value="ffff00"><?php $ucms->cout("admin.text-editor.forecolor.yellow"); ?></option>
		</select>
		<select class="edits" style="width: 150px;" id="backcolor">
			<option selected><?php $ucms->cout("admin.text-editor.backcolor"); ?></option>
			<option value="ff0000"><?php $ucms->cout("admin.text-editor.backcolor.red"); ?></option>
			<option value="0000ff"><?php $ucms->cout("admin.text-editor.backcolor.blue"); ?></option>
			<option value="00ff00"><?php $ucms->cout("admin.text-editor.backcolor.green"); ?></option>
			<option value="ffffff"><?php $ucms->cout("admin.text-editor.backcolor.white"); ?></option>
			<option value="000000"><?php $ucms->cout("admin.text-editor.backcolor.black"); ?></option>
			<option value="ffff00"><?php $ucms->cout("admin.text-editor.backcolor.yellow"); ?></option>
		</select>
		<br><br>
		<nobr>
		<?php
		foreach ($controls as $name => $function){
			?>
			<input title="<?php echo htmlspecialchars($ucms->cout("admin.text-editor.$name", true)); ?>" 
			class="editb" type="button" <?php if($name == 'html') echo 'id="htmlb"'; if($name == 'visual') echo 'id="wysiwygb"'; ?> 
			style="background: url(<?php echo $link.$name.'.gif'; ?>); <?php if($name == 'visual') echo 'display:none;'; ?>" onclick="<?php echo $function; ?>">
			<?php
		}
		?>
		</nobr>
		<?php
	}

	/**
	 *
	 * Load input to store editor text
	 * @package uCMS
	 * @since uCMS 1.3
	 * @version uCMS 1.3
	 * @return nothing
	 *
	*/
	function text_input($value = ''){
		echo '<input type="hidden" name="body" id="body" value="'.$value.'">';
	}
}
?>