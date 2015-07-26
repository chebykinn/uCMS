<table class="manage summary">
	<tr>
		<th class="always-show"><?php p("Summary"); ?></th>
	</tr>
	<tr>
		<td class="always-show">
			<p>
				<?php echo "μCMS $coreVersion"; ?>
			</p>
			<p>
				<?php
				foreach ($stats as $name => $amount) {
					echo '<div class="info">'.tr($name).':<span class="amount">'.$amount.'</span></div>';
				}
				?>
			</p>
		</td>
	</tr>
</table>