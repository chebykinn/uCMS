<?php
include $theme->get_path().'head.php';
include $theme->get_path().'nav.php';

?>
<div id="content">
<br><h2 style="text-align: center;"><?php $ucms->cout("theme.ucms.register.header"); ?></h2><br>
<?php
$reg->registration_test();
$reg->registration_form();
?>
</div>
<?php
include $theme->get_path().'sidebar.php';
include $theme->get_path().'footer.php';
?>
