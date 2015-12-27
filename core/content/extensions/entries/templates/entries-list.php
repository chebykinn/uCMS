<div class="entries">
<?php
if( $entriesAmount > 0 ){
	foreach ($entries as $entry) {
		// Flag to cut content when listing entries
		$isShort = !$isEntryPage;
		?>
		<div class="entry" id="<?php print $this->prepare($entry->eid); ?>">
			<div class="header">
				<div class="categories">
					<?php print $this->prepare(tr('Uncategorized')); ?>
				</div>
				<div class="title">
				<?php
					if( $isEntryPage ){
						print $this->prepare($entry->title);
					}else{
						?>
						<a href="<?php print $this->prepare($entry->getLink()); ?>" title="<?php p("View entry"); ?>"><?php
						print $this->prepare($entry->title); ?></a>
						<?php
					}
				?>
				</div>
				<div class="date">
				<?php print $this->prepare($entry->getDate()); ?>
				</div>
			</div>
			<div class="content">
				<?php print $entry->getContent($isShort); ?>
			</div>
			<div class="info">
				<div class="comments">
					<a href="<?php print $this->prepare($entry->getLink()); ?>#comments" title="<?php p("View comments"); ?>"><?php
					print $this->prepare($commentsCount); ?></a>
				</div>
				<div class="tags">
				<?php
					p('no tags');
				?>
				</div>
				<?php
					if( $entry->uid === $currentUser->uid || $currentUser->can('manage entries') ){
				?>
				<div class="admin">
					<?php 
						$editLink = $entry->getEditLink();
						print '<a class="edit-link" href="'.$editLink.'" title="'.tr('Edit this entry').'">'.tr('Edit').'</a>';
					?>
				</div>
				<?php
					}
				?>
			</div>
		</div>
		<?php
		if( $isEntryPage ){ ?>
			<div id="comments">
				<?php
					if( count($comments) > 0 ){
						foreach ($comments as $comment) {
							print $this->prepare($comment->name).'<br>'.$this->prepare($comment->content);
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