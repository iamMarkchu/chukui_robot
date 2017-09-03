<?php
/**
 * 73288  89256
 */
ini_set("memory_limit", "1024M");
require dirname(__FILE__).'/../core/init.php';

/* Do NOT delete this comment */
/* 不要删除这段注释 */
if(isset($argv[1]))
   $id = $argv[1];
else
   $id = 31934;
$url = 'http://www.mzitu.com/'.$id;
$html = requests::get($url);
$data = selector::select($html, "//div[contains(@class, 'pagenavi')]/a[last()-1]/span");
$count = intval($data);

$immage_list = [];
for ($i=1; $i <= $count; $i++) {
	if($i == 1)
	{
		$tmp_url = $url;
	}else{
		$tmp_url = $url.'/'.$i;
	}
	$html = requests::get($tmp_url);
	$data = selector::select($html, "//div[contains(@class, 'main-image')]/p/a/img");
	$immage_list[] = $data;
}

$str = '';

foreach ($immage_list as $k => $v) {
	$str .= '<img src="'.$v.'" width="600" referrer="http://www.mzitu.com"/>';
}
$file = file_get_contents('./template.html');
$file = str_replace('kkk', $str, $file);
echo file_put_contents('./view.html', $file);
echo '123';
