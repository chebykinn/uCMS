</td>
<td id="sidebar-cell">
<div class="sidebar">
	<div class="widg-top"></div>
	<div class="wtitle">
		<b><a title="" onclick="$('#profile').slideToggle('slow');" ><?php $ucms->cout("theme.ucms.sidebar.user_menu"); ?></a></b>
	</div>
	<div class="widg">
	<div id="profile">
	<br>
	<?php
		$widget->load("user_menu");
	?>
	</div>
	</div>
	<div class="widg-bottom"></div>

	<div class="widg-top"></div>
    <div class="wtitle">
		<b><a title="" onclick="$('#cats').slideToggle('slow');" ><?php $ucms->cout("theme.ucms.sidebar.post_categories"); ?></a></b>
	</div>
	<div class="widg">
	<div id="cats">
		<?php
		$widget->load("post_categories");
		?>
	</div>
	</div>
	<div class="widg-bottom"></div>

	<div class="widg-top"></div>
    <div class="wtitle">
		<b><a title="" onclick="$('#archives').slideToggle('slow');" ><?php $ucms->cout("theme.ucms.sidebar.post_archives"); ?></a></b>
	</div>
	<div class="widg">
	<div id="archives">
		<?php
			$widget->load("post_archives");
		?>
		</div>
	</div>
	<div class="widg-bottom"></div>

	<div class="widg-top"></div>
    <div class="wtitle">
		<b><a title="" onclick="$('#tags').slideToggle('slow');" ><?php $ucms->cout("theme.ucms.sidebar.post_tags"); ?></a></b>
	</div>
	<div class="widg">
	<div id="tags">
	<?php
		$widget->load("post_tags");
		?>
	</div>
	</div>
	<div class="widg-bottom"></div>

	<div class="widg-top"></div>
    <div class="wtitle">
		<b><a title="" onclick="$('#users-online').slideToggle('slow');" ><?php $ucms->cout("theme.ucms.sidebar.site_stats"); ?></a></b>
	</div>
	<div class="widg">
	<div id="users-online">
	<?php
		$widget->load("site_stats");
		?>
	</div>
	</div>
	<div class="widg-bottom"></div>

	<div class="widg-top"></div>
    <div class="wtitle">
		<b><a title="" onclick="$('#site-links').slideToggle('slow');" ><?php $ucms->cout("theme.ucms.sidebar.site_links"); ?></a></b>
	</div>
	<div class="widg">
	<div id="site-links">
	<?php
		$widget->load("site_links");
		?>
	</div>
	</div>
	<div class="widg-bottom"></div>
</div>
</td>