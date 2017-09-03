<?php
ini_set('memory_limit', '1024M');
require dirname(__FILE__).'/../core/init.php';

$configs = array(
    'name' => 'mzitu',
    'log_show' => true,
    'log_type' => 'error,debug',
    'tasknum' => 5,
    'timeout' => 10,
    'max_try' => 5,
    'max_depth' => 3,
    'export' => array(
        'type' => 'db',
        'table' => 'mzitu'
    ),
    'domains' => array(
    	'www.mzitu.com',
    	'mzitu.com',
    ),
    'scan_urls' => array(
    	'http://www.mzitu.com/xinggan/',
        'http://www.mzitu.com/japan/',
        'http://www.mzitu.com/taiwan/',
        'http://www.mzitu.com/mm/',
    ),
    'list_url_regexes' => array(
        'http://www.mzitu.com/xinggan/(page/\d+/)',
        'http://www.mzitu.com/japan/(page/\d+/)',
        'http://www.mzitu.com/taiwan/(page/\d+/)',
        'http://www.mzitu.com/mm/(page/\d+/)',
    ),
    'content_url_regexes' => array(
    	'http://www.mzitu.com/\d+'
    ),
    'fields' => array(
    	array(
    		'name' => 'title',
    		'selector' => "//h2[contains(@class,'main-title')]",
    		'required' => true,
    	), 
    	array(
    		'name' => 'image',
    		'selector' => "//div[contains(@class,'main-image')]/p/a/img",
    		'required' => true,
    	),
        array(
            'name' => 'category',
            'selector' => "//div[contains(@class,'main-meta')]/span[1]/a",
            'required' => true,
        ),
        array(
            'name' => 'publish_time',
            'selector' => "//div[contains(@class,'main-meta')]/span[2]",
            'required' => true,
        ),
        array(
            'name' => 'view_count',
            'selector' => "//div[contains(@class,'main-meta')]/span[3]",
            'required' => true,
        ),

    )
);

$spider = new phpspider($configs);
$spider->on_scan_page = function($page, $content, $phpspider)
{
    if(!preg_match('/\/page\/\d+/', $page['url']))
    {
        $data = selector::select($content, "//div[contains(@class, 'nav-links')]/a[last()-1]");
        preg_match('/\d+/', $data, $match);
        for ($i=5; $i <=$match[0] ; $i++) {
            $tmp_url = $page['url']."page/".$i."/";
            $phpspider->add_url($tmp_url);
        }
    }
};
$spider->on_content_page = function($page, $content, $phpspider)
{
    if(!preg_match('/\/\d+\/\d+/', $page['url']))
    {
        $data = selector::select($content, "//div[contains(@class,'pagenavi')]/a[last()-1]/span");
        $data = intval($data);
        for ($i=2; $i <=$data ; $i++) { 
            $tmp_url = $page['url']."/".$i;
            $phpspider->add_url($tmp_url);   
        }
    }
    return false;
};

$spider->on_extract_page = function($page, $data)
{
    $data['url'] = $page['url'];
    $data['publish_time'] = str_replace('发布于 ', '', $data['publish_time']);
    $data['view_count'] = str_replace('次浏览', '', $data['view_count']);
    return $data;
};
$spider->start();
