<div class="entries">
<?php
if( $entriesAmount > 0 ){
	foreach ($entries as $entry) {
		// Flag to cut content when listing entries
		$isShort = !$isEntryPage;
		?>
		<div class="entry">
			<p class="title">
				<a href="<?php echo $entry->getLink(); ?>" title="<?php echo $entry->title; ?>"><?php echo $entry->title; ?></a>
			</p>
			<p class="content">
				<?php echo $entry->getContent($isShort); ?>
			</p>
			<p class="info">
				<?php $delimeter = '<span class="delimeter"> | </span>'; ?>
				<?php echo tr('Date:').' '.$entry->getDate().$delimeter.tr('Tags:').' none'; ?>
			</p>
		</div>
		<?php
		if( $isEntryPage ){ ?>
			<div class="comments">
				<?php echo 'not implemented yet'; ?>
			</div>
			<?php
		}
	}
}else{
	?>
	<div class="warning">
		<?php p("There are no entries to display."); ?>
	</div>
	<?php
}

?>
</div>