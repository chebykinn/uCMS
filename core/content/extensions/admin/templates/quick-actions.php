<table class="manage actions">
	<tr>
		<th class="always-show"><?php p("Quick Actions"); ?></th>
	</tr>
	<tr>
		<td class="always-show">
			<ul>
			<?php 
			foreach ($links as $link) {
				?>
				<li><a href="<?php echo $link->getLink(); ?>"><?php p($link->title); ?></a></li>
				<?php
			} 
			?>
			</ul>
		</td>
	</tr>
</table>