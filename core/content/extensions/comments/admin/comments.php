<?php
use uCMS\Core\Admin\ManagePage;
use uCMS\Core\Admin\ManageTable;
use uCMS\Core\Extensions\Comments\Comment;
use uCMS\Core\Setting;
$page = new ManagePage();
$table = new ManageTable();
$page->doActions();

$amount = (new Comment())->count();

$table->addSelectColumn('manage comments');
$table->setInfo("amount", $amount);

$table->addColumn($this->tr('Content'), true, 'manage comments', 0, true);
$table->addColumn($this->tr('Author'), true, 'manage comments', 0, true);
$table->addColumn($this->tr('Entry'), true, 'manage comments', 0, true);
$table->addColumn($this->tr('Email'), true, 'manage comments', 0, true);
$table->addColumn($this->tr('IP'), true, 'manage comments', 0, true);
$table->addColumn($this->tr('Added'), true, 'manage comments', 0, true);

$limit = Setting::Get('per_page');
$comments = (new Comment())->find(array('limit' => $limit));
foreach ($comments as $comment) {
	$table->setInfo("idKey", $comment->fid);
	$table->setInfo("status", $comment->status);
	$table->addRow( [
			$comment->content,
			$comment->name,
			$comment->entry->title,
			$comment->email,
			$comment->ip,
			$comment->getDate()
		]
	);
}
$table->printTable();
?>