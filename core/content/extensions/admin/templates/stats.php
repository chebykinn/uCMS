<table class="manage summary">
	<tr>
		<th class="always-show"><?php $this->p("Summary"); ?></th>
	</tr>
	<tr>
		<td class="always-show">
			<p>
				<?php echo "μCMS $coreVersion at $siteName"; ?>
			</p>
			<p>
				<?php
					echo '<div class="info">'.$this->tr('Time').':<span class="amount">'.$currentTime.'</span></div>';
					echo '<div class="info">'.$this->tr('Theme').':<span class="amount">'.$currentTheme.'</span></div>';
					echo '<div class="info">'.$this->tr('Domain').':<span class="amount">'.$domain.'</span></div>';
					echo '<div class="info">'.$this->tr('Directory').':<span class="amount">'.$directory.'</span></div>';
				?>
			</p>
			<p>
				<?php
				foreach ($stats as $name => $amount) {
					echo '<div class="info">'.$this->tr($name).':<span class="amount">'.$amount.'</span></div>';
				}
				?>
			</p>
		</td>
	</tr>
</table>