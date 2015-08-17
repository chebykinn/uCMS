<?php 
ob_start();
phpinfo(); 
$info = ob_get_clean();
$outinfo = array(1 => '');
preg_match('/<body[^>]*>(.*?)<\/body>/is', $info, $outinfo);
$out = str_replace('<table', '<table class="manage"', $outinfo[1]);
$out = str_replace('<hr />', "", $out);
echo $out; 
?>