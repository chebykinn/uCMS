<?php
if( empty($models) ):
	echo '<table class="manage"><tr><td class="always-show">';
	$this->p('No content selected to display.');
	echo '</td></tr></table>';
else:
	foreach ($models as $title => $data) {
		?>
		<table class="manage last-added">
		<tr>
		<th class="always-show"><?php $this->p($title); ?></th>
		</tr>
		<?php
		if ( !empty($data['rows']) ){
			foreach ($data['rows'] as $row) {
				echo '<tr><td class="always-show">';
				if( file_exists($data['template']) ){
					include $data['template'];
				}
				echo '</td></tr>';
			}
		}else{
			echo '<tr><td class="always-show">';
			$this->p('No elements');
			echo '<br><br></td></tr>';
		}
		echo '</table>';
	}
endif;
?>