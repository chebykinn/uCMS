<table class="manage summary">
	<tr>
		<th class="always-show"><?php p("Summary"); ?></th>
	</tr>
	<tr>
		<td class="always-show">
			<p>
				<?php echo 'μCMS '.CORE_VERSION; ?>
			</p>
			<p>
				<span class="info"><?php p("Users: @s",      $this->usersAmount); ?></span>
				<span class="info"><?php p("Groups: @s",     $this->groupsAmount);?></span>
				<span class="info"><?php p("Categories: @s", 0); ?></span>
				<span class="info"><?php p("Posts: @s",      0); ?></span>
				<span class="info"><?php p("Pages: @s",      0); ?></span>
				<span class="info"><?php p("Comments: @s",   0); ?></span>
				<span class="info"><?php p("Links: @s",      0); ?></span>
				<span class="info"><?php p("Extensions: @s", $this->extensionsAmount); ?></span>
				<span class="info"><?php p("Themes: @s",     0); ?></span>
				<span class="info"><?php p("Widgets: @s",    0); ?></span>
			</p>
		</td>
	</tr>
</table>