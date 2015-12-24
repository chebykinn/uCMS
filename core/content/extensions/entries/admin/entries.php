<?php
use uCMS\Core\Admin\ManagePage;
use uCMS\Core\Admin\ManageTable;
use uCMS\Core\Database\Query;
use uCMS\Core\Settings;
use uCMS\Core\Form;
use uCMS\Core\Page;
use uCMS\Core\Notification;
use uCMS\Core\Extensions\Entries\Entry;
use uCMS\Core\Extensions\Users\User;
use uCMS\Core\Extensions\ExtensionHandler;
use uCMS\Core\Extensions\Entries\EntryType;
use uCMS\Core\Extensions\Comments\Comment;
use uCMS\Core\Admin\ControlPanel;
$page = new ManagePage();
$table = new ManageTable();
$page->addSection('add', 'manage entries', tr('Add New Entry'));
$page->addSection('edit', 'manage entries', tr('Edit Entry'));
$page->addSection('type/add', 'manage entries', tr('Add Entry Type'));
$page->addSection('type/edit', 'manage entries', tr('Edit Entry Type'));

$page->addAction('delete', 'manage entries', 'delete');
$page->doActions();
$page->checkSection();

$page->printSectionTitle();

switch ($page->getSection()) {
	case 'add': case 'edit':
		$action = $page->getSection();
		if( isset($_POST[$action.'-entry']) ){
			$required = ['title', 'content'];
			$notAll = false;
			foreach ($required as $key) {
				if( empty($_POST[$key]) ){
					$notAll = true;
				}
			}
			if( $notAll ){
				$errMsg = new Notification(tr("You should fill title and text fields."), Notification::ERROR);
				$errMsg->add();
				Page::Refresh();
			}
			if( $action == 'edit' ){
				$eid = intval(Page::GetCurrent()->getKeyValue($action));
				$entry = (new Entry())->find($eid);
				if( $entry == NULL ){
					$errMsg = new Notification(tr("Entry doesn't exists."), Notification::ERROR);
					$errMsg->add();
					Page::Refresh();
				}
			}else{
				$entry = (new Entry())->empty();
			}
			$entry->title = $_POST['title'];
			$entry->status = 1;
			$entry->comments = 1;
			$entry->alias = isset($_POST['alias']) ? $_POST['alias'] : '';
			$entry->content = $_POST['content'];
			$result = true;
			if( $action == 'edit' ){
				$entry->update();
			}else{
				$result = $entry->create();
			}
			if( !$result ){
				$errMsg = new Notification(tr('Unable to add entry.'), Notification::ERROR);
				$errMsg->add();
				Page::Refresh();
			}
			$msg = new Notification(tr('Entry was successfully added.'), Notification::SUCCESS);
			$msg->add();
			$backPage = $page->getSectionPage();
			$backPage->go();
		}

		$savedTitle = "";
		$savedAlias = "";
		$savedContent = "";
		$savedType = "";
		if( $action == 'edit' ){
			$eid = intval(Page::GetCurrent()->getKeyValue($action));
			$entry = (new Entry())->find($eid);
			if( $entry == NULL ){
				$backPage = $page->getSectionPage();
				$backPage->go();
			}
			$savedTitle = $entry->title;
			$savedAlias = $entry->alias;
			$savedContent = $entry->content;
			$savedType = $entry->type;
		}
		$entries = ExtensionHandler::Get('entries');
		$tinymcePath = $entries->getURLFilePath($entries->getInfo('tinymce'));
		$buttonLabel = $action == 'add' ? 'Add' : 'Update';
		$types = (new EntryType())->find(['orders' => ['type' => 'ASC']]);
		$list = [];
		foreach ($types as $type) {
			$list[tr($type->name)] = $type->type;
		}
		$form = new Form($action.'-entry', '', tr($buttonLabel));
		$form->addField("title", "text", tr("Title:"), "", $savedTitle);
		$form->addField("alias", "text", tr("Link:"), "", $savedAlias, "", false);
		$form->addField("content", "textarea", "", "", $savedContent, "", false);
		$form->addSelectField($list, "type", tr("Type:"), "", $savedType);
		$form->addField("tags", "text", tr("Tags:"), "", "", "", false);
		$form->render();
		?>
		<script type="text/javascript" src="<?php echo $tinymcePath; ?>"></script>
		<script>
		tinymce.init({
			selector:'textarea',
			height: 350,
			plugins: [
 			  'advlist autolink lists link image charmap print preview anchor',
 			  'searchreplace visualblocks code fullscreen',
 			  'insertdatetime media table contextmenu paste code'
 			]
		});

		</script>
		<?php
	break;
	
	default:
		$page->addButton(tr('Add'), $page->getSectionPage('add'));
		$table->setInfo("amount", Settings::Get('entries_amount'));
		$table->addSelectColumn('manage entries');
		$table->addColumn(tr('Title'), true, 'manage entries', '20%', true);
		$table->addColumn(tr('Type'), true, 'manage entries', true);
		$table->addColumn(tr('Author'), true, 'manage entries', true);
		$table->addColumn(tr('Terms'), true, 'manage entries');
		$table->addColumn(tr('Comments'), true, 'manage entries');
		$table->addColumn(tr('Created'), true, 'manage entries', '15%');
		
		$limit = Settings::Get("entries_per_page");
		//array('where' => array('column' => 'type', 'operator' => '=', 'value' => 'article')
		$entries = (new Entry())->find(array('limit' => $limit, 'orders' => array('created' => 'desc')));
		
		foreach ($entries as $entry) {
			$table->setInfo("idKey", $entry->eid);
			$table->setInfo('status', $entry->status);
			$table->addRow( array(
				'<a href="'.$entry->getLink().'">'.$entry->title.'</a><br><div class="manage-actions">'.
				$table->manageButtons(array(
					tr("Publish")."|".tr("Draft") => 'switch-status',
					tr("Edit") => 'edit',
					tr("Delete") => 'delete'
					)).'</div>',
				$entry->entryType->name,
				$entry->author->name,
				"",
				(new Comment())->count(array('eid' => $entry->eid)),
				$entry->getDate()
				)
			);
		}
		$table->printTable();
	break;
}

function delete($eid){
	$entry = (new Entry())->find($eid);
	if( $entry == NULL){
		$errMsg = new Notification(tr('Error: there is no such entry.'), Notification::ERROR);
		$errMsg->add();
		return false;
	}
	$entry->delete();
	return true;

}
?>