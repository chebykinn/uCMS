<?php
include $theme->get_path().'head.php';
include $theme->get_path().'nav.php';
?>
<div id="content">
<br><h2 style="text-align: center;"><?php $ucms->cout("theme.ucms.login.header"); ?></h2><br>
<?php
$login->login_test();
echo "<br>";
$login->login_form();
?>
</div>
<?php
include $theme->get_path().'sidebar.php';
include $theme->get_path().'footer.php';
?>
