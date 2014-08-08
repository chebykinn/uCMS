<form method="get" action="<?php echo UCMS_DIR; ?>/">
	<input type="hidden" name="action" value="search">
	<input class="search-field" type="text" name="query" value="<?php echo get_query(); ?>" placeholder="<?php $ucms->cout("module.search.form.input.placeholder") ?>"><button type="submit" class="search-button" ><?php $ucms->cout("module.search.form.search.button") ?></button>
	<?php
	$modules = get_modules_to_search();
	global $module;
	if(count($modules) > 1){
		echo "<br><br><b>".$ucms->cout("module.search.form.search_in.label", true)."</b> ";
		foreach ($modules as $mod) {
			echo ' <input type="radio" name="module" value="'.$mod.'" '.($mod == $module ? 'checked' : '').'> '.get_module("local_name", $mod);
		}
	}
	echo "</form><br>";
?>