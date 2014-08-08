<?php
include $theme->get_path().'head.php';
include $theme->get_path().'nav.php';
?>
<div id="content">
   <h1 style="text-align:center;"><?php $ucms->cout("theme.ucms.error.404.header"); ?></h1>
		<?php $ucms->cout("theme.ucms.error.404.message", false, ADMIN_EMAIL); ?>
  </div>
<?php
include $theme->get_path().'sidebar.php';
include $theme->get_path().'footer.php';
?>