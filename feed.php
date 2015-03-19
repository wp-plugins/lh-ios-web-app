<?php


$url = $_GET['url'];

function lh_strictify_content($content){

if (!class_exists('HTMLPurifier_Bootstrap')) {

require_once 'lib/HTMLPurifier.auto.php';

}

$config = HTMLPurifier_Config::createDefault();

$config->set('HTML.AllowedElements', 'a,h1,h2,h3,h4,address,blockquote,img,em,strong,u,ol,ul,dl,dt,dd,li,p,br,table,tbody,td,tr,span,div');


$config->set('Attr.EnableID', true);

//$config->set('Core.Encoding', 'UTF-8'); 

$config->set('HTML.AllowedAttributes', 'a.href,a.title,a.class,img.src,img.height,img.width,img.align,img.alt,img.class, ul.class,p.align,p.class,span.class,div.id,div.class,h1.class,h2.class,h3.class,h4.class');

$purifier = new HTMLPurifier($config);

$content = $purifier->purify($content);

$content = preg_replace('/[\r\n\s\t]+/xms', ' ', trim($content));  

$pattern = "/<p[^>]*><\\/p[^>]*>/"; 

$content = preg_replace($pattern, '', $content); 

$pattern = "/<a[^>]*><\\/a[^>]*>/"; 

$content = preg_replace($pattern, '', $content); 

$pattern = "/<p[^>]*> <\\/p[^>]*>/"; 

$content = preg_replace($pattern, '', $content); 

return $content;

}



function lh_content_curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}


function lh_content_absolute_url($txt, $base_url){ 
$needles = array('href="', 'src="', 'background="', 'href=\'',); 
$new_txt = ''; 
if(substr($base_url,-1) != '/') $base_url .= '/'; 
$new_base_url = $base_url; 
$base_url_parts = parse_url($base_url); 

foreach($needles as $needle){ 
while($pos = strpos($txt, $needle)){ 
$pos += strlen($needle); 
if(substr($txt,$pos,7) != 'http://' && substr($txt,$pos,8) != 'https://' && substr($txt,$pos,6) != 'ftp://' && substr($txt,$pos,9) != 'mailto://'){ 
if(substr($txt,$pos,1) == '/') $new_base_url = $base_url_parts['scheme'].'://'.$base_url_parts['host']; 
$new_txt .= substr($txt,0,$pos).$new_base_url; 
} else { 
$new_txt .= substr($txt,0,$pos); 
} 
$txt = substr($txt,$pos); 
} 
$txt = $new_txt.$txt; 
$new_txt = ''; 
} 
return $txt; 
} 





function lh_content_feed_get_url($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_FOLLOWLOCATION => TRUE,  // the magic sauce
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_SSL_VERIFYHOST => FALSE, // suppress certain SSL errors
        CURLOPT_SSL_VERIFYPEER => FALSE, 
        CURLOPT_COOKIEJAR => 'cookies.txt',
        CURLOPT_COOKIEFILE => 'cookies.txt'
    ));


	$data = curl_exec($ch);
	curl_close($ch);
	return $data;

}


function lh_content_harvest_url($url){

$foo = lh_content_feed_get_url($url);

$parse = parse_url($url);

$foo = lh_content_absolute_url($foo, "http://".$parse[host]);

$file = $parse[host].".php";

if (file_exists('feeds/'.$file) ) {

include 'feeds/'.$file;

$bar = lh_content_extractor_create_feed($foo);

}

}

$foo = lh_content_harvest_url($url);


echo $foo;




?>

