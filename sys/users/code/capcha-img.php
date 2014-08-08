<?php
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];
define("code_dir", "capcha/");
session_start();
function generate_code(){            
	$hours = date("H");    
    $minuts = substr(date("H"), 0 , 1); 
    $months = date("F");            
    $year_day = date("z"); 
    $str = $hours . $minuts . $months . $year_day;
    $str = trim(sha1((strrev(md5($str)))));
	$str = substr($str, 3, 6);
    $array_mix = preg_split('//', $str, -1, PREG_SPLIT_NO_EMPTY);
    srand ((float)microtime()*1000000);
    shuffle ($array_mix);
    return implode("", $array_mix);
}

function img_code(){
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");                   
	header("Last-Modified: " . gmdate("D, d M Y H:i:s", 10000) . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");         
	header("Cache-Control: post-check=0, pre-check=0", false);           
	header("Pragma: no-cache");                                           
	header("Content-Type:image/png");
	$linenum = 2;
	$img_arr = array(
                 "capcha.png",
                 "capcha0.png"
					);
	$font_arr = array();
	$font_arr[0]["fname"] = "verdana.ttf";
	$font_arr[0]["size"] = 16;
	$font_arr[1]["fname"] = "times.ttf";
	$font_arr[1]["size"] = 16;
	$n = rand(0,sizeof($font_arr)-1);
	$img_fn = $img_arr[rand(0, sizeof($img_arr)-1)];
	$im = imagecreatefrompng (code_dir . $img_fn);
	for ($i=0; $i<$linenum; $i++){
		$color = imagecolorallocate($im, rand(0, 150), rand(0, 100), rand(0, 150));
		imageline($im, rand(0, 20), rand(1, 50), rand(150, 180), rand(1, 50), $color);
	}
	$_SESSION['captcha-code'] = generate_code();
	$color = imagecolorallocate($im, rand(0, 200), 0, rand(0, 200));
	imagettftext ($im, $font_arr[$n]["size"], rand(-4, 4), rand(10, 45), rand(20, 35), $color, code_dir.$font_arr[$n]["fname"], $_SESSION['captcha-code']);
	for ($i = 0; $i < $linenum; $i++){
		$color = imagecolorallocate($im, rand(0, 255), rand(0, 200), rand(0, 255));
		imageline($im, rand(0, 20), rand(1, 50), rand(150, 180), rand(1, 50), $color);
	}
	ImagePNG ($im);
	ImageDestroy ($im);
}
img_code();

?>
