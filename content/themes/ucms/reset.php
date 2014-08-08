<?php
include THEMEPATH.'head.php';
include THEMEPATH.'nav.php';
?>

<div id="content">
<h2>Забыли пароль</h2>
<?php 
$user->reset_password();
$user->reset_form();
?>
</div>
<?php
include THEMEPATH.'sidebar.php';
include THEMEPATH.'footer.php';
?>