<?php
include THEMEPATH.'head.php';
include THEMEPATH.'nav.php';

?>
<div id="content">
<br><h2 style="text-align: center;">Регистрация</h2><br>
<?php
$reg->registration_test();
$reg->registration_form();
?>
</div>
<?php
include THEMEPATH.'sidebar.php';
include THEMEPATH.'footer.php';
?>
