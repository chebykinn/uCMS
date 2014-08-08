<h2><?php $ucms->cout("module.search.settings.header"); ?></h2>
<form action="settings.php?module=search" method="post">

	<!-- <input name="searchin" type="hidden" value="posts"> -->
	<?php
		$modules = get_modules();
		if($modules){
			for ($i = 0; $i < count($modules); $i++) { 
				$file = ABSPATH.MODULES_PATH.$modules[$i]['dir'].'/search.txt';
				if(file_exists($file)){
					$strings = file($file);
					if(is_array($strings) and count($strings) >= 6){
						$names[] = $modules[$i]['local_name'];
						$ids[] = $modules[$i]['dir'];
					}
				}
			}
		}
		$search_in = explode(",", SEARCH_IN);

		if(!in_array(DEFAULT_SEARCH_MODULE, $search_in) and !empty(DEFAULT_SEARCH_MODULE)){
			$upd = $ucms->update_setting("default_search_module", $search_in[0]);
			header("Location: settings.php?module=search");
		}
	?>
	<table class="forms">
		<tr>
		<td style="width: 250px"><?php $ucms->cout("module.search.settings.searchin.name"); ?></td>
		<td>
			<?php
				for ($i = 0; $i < count($ids); $i++) { 
					echo '<input name="searchin[]" type="checkbox" value="'.$ids[$i].'" '.(in_array($ids[$i], $search_in) ? "checked" : "").'> '.$names[$i].'<br><br>';
				}
			?>
		</td>
		<td><?php $ucms->cout("module.search.settings.searchin.description"); ?></td>
		</tr>
		<tr>
		<td style="width: 250px"><?php $ucms->cout("module.search.settings.default_search_module.name"); ?></td>
		<td>
			<?php
				for ($i = 0; $i < count($ids); $i++) {
					if(in_array($ids[$i], $search_in)){
						echo '<input name="default_search_module" type="radio" value="'.$ids[$i].'" '.(DEFAULT_SEARCH_MODULE == $ids[$i] ? "checked" : "").'> '.$names[$i].'<br><br>';
					}
				}
			?>
		</td>
		<td><?php $ucms->cout("module.search.settings.default_search_module.description"); ?></td>
		</tr>
		<tr>
		<td><?php $ucms->cout("module.search.settings.results_on_page.name"); ?></td>
		<td><input type="number" name="results_on_page" min="1" value="<?php echo RESULTS_ON_PAGE; ?>"></td>
		<td><?php $ucms->cout("module.search.settings.results_on_page.description"); ?></td>
		</tr>
		<tr>
			<td colspan="2"><input class="ucms-button-submit" type="submit" name="settings-update" value="<?php $ucms->cout("module.search.settings.apply.button"); ?>"></td>
		</tr>
	</table>
</form>