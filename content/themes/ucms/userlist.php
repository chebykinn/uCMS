<?php
include THEMEPATH.'head.php';
include THEMEPATH.'nav.php';
?>
<div id="content">
<h2>Список пользователей</h2> 
<?php $user->list_users(); ?>
</div>
<?php
include THEMEPATH.'sidebar.php';
include THEMEPATH.'footer.php';
?>
