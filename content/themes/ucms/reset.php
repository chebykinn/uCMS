<?php
include $theme->get_path().'head.php';
include $theme->get_path().'nav.php';
?>

<div id="content">
<h2><?php $ucms->cout("theme.ucms.reset.header"); ?></h2>
<?php 
$user->reset_password();
$user->reset_form();
?>
</div>
<?php
include $theme->get_path().'sidebar.php';
include $theme->get_path().'footer.php';
?>