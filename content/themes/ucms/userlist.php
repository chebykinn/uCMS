<?php
include $theme->get_path().'head.php';
include $theme->get_path().'nav.php';
?>
<div id="content">
<h2><?php $ucms->cout("theme.ucms.userlist.header"); ?></h2><br> 
<?php $user->list_users(); ?>
</div>
<?php
include $theme->get_path().'sidebar.php';
include $theme->get_path().'footer.php';
?>
