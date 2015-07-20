<?php 
ob_start();
phpinfo(); 
$info = ob_get_clean();
$outinfo = array(1 => '');
preg_match('/<body[^>]*>(.*?)<\/body>/is', $info, $outinfo);
echo $outinfo[1]; 
?>