<?php
include THEMEPATH.'head.php';
include THEMEPATH.'nav.php';
?>
<div id="content">
<br><h2 style="text-align: center;">Вход</h2><br>
<?php
$login->login_test();
echo "<br>";
$login->login_form();
?>
</div>
<?php
include THEMEPATH.'sidebar.php';
include THEMEPATH.'footer.php';
?>
