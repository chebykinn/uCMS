<div class="sidebar">
	<?php
		/*for ($i = 0; $i < $widget->get_widgets_count(); $i++) { 
			?>
			<div class="widg-top"></div>
			<div class="wtitle">
				<b><a title="" onclick="$('#<?php echo $widget->get('dir', $i); ?>').slideToggle('slow');" ><?php echo $widget->get('name', $i); ?></a></b>
			</div>
			<div class="widg">
			<div id="<?php echo $widget->get('dir', $i); ?>">
			<br>
			<?php
				$widget->load($i);
			?>
			</div>
			</div>
			<div class="widg-bottom"></div>
			<?php
		}*/
	?>
	<div class="widg-top"></div>
	<div class="wtitle">
		<b><a title="" onclick="$('#profile').slideToggle('slow');" >Профиль</a></b>
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
		<b><a title="" onclick="$('#cats').slideToggle('slow');" >Категории</a></b>
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
		<b><a title="" onclick="$('#archives').slideToggle('slow');" >Архивы</a></b>
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
		<b><a title="" onclick="$('#tags').slideToggle('slow');" >Облако тегов</a></b>
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
		<b><a title="" onclick="$('#users-online').slideToggle('slow');" >Статистика</a></b>
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
		<b><a title="" onclick="$('#site-links').slideToggle('slow');" >Ссылки</a></b>
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