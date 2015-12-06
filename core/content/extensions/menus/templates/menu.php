<ul>
	<?php
	// TODO: Tree structure
	foreach ($links as $link) {
		$selected = $link->isCurrentPage() ? ' class="selected"' : '';
		echo '<li><a '.$selected.' href="'.$link->getLink().'" title="'.tr($link->title).'">'.tr($link->title).'</a></li>';
	}
	?>
</ul>