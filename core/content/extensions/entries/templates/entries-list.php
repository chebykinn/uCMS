<div class="entries">
<?php
if( $entriesAmount > 0 ){
	foreach ($entries as $entry) {
		// Flag to cut content when listing entries
		$isShort = !$isEntryPage;
		?>
		<div class="entry">
			<p class="title">
				<?php
					if( $isEntryPage ){
						echo $entry->title;
					}else{
						?>
						<a href="<?php echo $entry->getLink(); ?>" title="<?php echo $entry->title; ?>"><?php echo $entry->title; ?></a>
						<?php
					}
				?>
			</p>
			<p class="content">
				<?php echo $entry->getContent($isShort); ?>
			</p>
			<p class="info">
				<?php $delimeter = '<span class="delimeter"> | </span>'; ?>
				<?php echo tr('Date:').' '.$entry->getDate().$delimeter.tr('Tags:').' none'; ?>
				<?php
					if( $entry->uid === $currentUser->uid || $currentUser->can('manage entries') ){
						$editLink = $entry->getEditLink();
						echo '<a href="'.$editLink.'" title="'.tr('Edit this entry').'">'.tr('Edit').'</a>';
					}
				?>
			</p>
		</div>
		<?php
		if( $isEntryPage ){ ?>
			<div class="comments">
				<?php
					if( count($comments) > 0 ){
						foreach ($comments as $comment) {
							echo $comment->name.'<br>'.$comment->content;
						}
					}else{
						p('No comments');
					}
				?>
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