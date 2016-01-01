<?php
/*
* Variables:
* $currentMenu - current Menu object
* $links - array of MenuLink for current Menu
*/
?>
<table class="manage actions">
	<tr>
		<th class="always-show"><?php $this->p($currentMenu->title); ?></th>
	</tr>
	<tr>
		<td class="always-show">
			<?php
				$currentMenu->render();
			?>
		</td>
	</tr>
</table>