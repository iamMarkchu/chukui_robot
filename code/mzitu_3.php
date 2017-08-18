<?php
ini_set('memory_limit', '1024M');
require dirname(__FILE__).'/../core/init.php';

$configs = array(
    'name' => 'mzitu',
    'log_show' => TRUE,
    'log_type' => 'error,debug',
    'client_ip' => array(
        '192.168.0.2', 
        '192.168.0.3',
        '192.168.0.4',
    ),
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
    	'http://www.mzitu.com/',
    ),
    'list_url_regexes' => array(
        'http://www.mzitu.com/(page/\d+/?)',
    ),
    'content_url_regexes' => array(
    	'http://www.mzitu.com/\d+',
    ),
    'fields' => array(
    	array(
    		'name' => 'title',
    		'selector' => "//h2[contains(@class,'main-title')]",
    		'required' => TRUE,
    	), 
    	array(
    		'name' => 'image',
    		'selector' => "//div[contains(@class,'main-image')]/p/a/img",
    		'required' => TRUE,
    	),
        array(
            'name' => 'category',
            'selector' => "//div[contains(@class,'main-meta')]/span[1]/a",
            'required' => TRUE,
        ),
        array(
            'name' => 'publish_time',
            'selector' => "//div[contains(@class,'main-meta')]/span[2]",
            'required' => TRUE,
        ),
        array(
            'name' => 'view_count',
            'selector' => "//div[contains(@class,'main-meta')]/span[3]",
            'required' => TRUE,
        ),

    )
);

$spider = new phpspider($configs);

$spider->on_scan_page = function($page, $content, $phpspider)
{
    $data = selector::select($content, "//div[contains(@class,'nav-links')]/a[last()-1]/text()");
    $data = intval($data);
    for ($i=$data;$i >1;$i--) {
        echo $page['url']. PHP_EOL;
        $tmp_url = $page['url']. "page/".$i;
        $phpspider->add_url($tmp_url);
    }
    return FALSE;
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
    return FALSE;
};

$spider->on_extract_page = function($page, $data)
{
    if(preg_match('/\/\d+\/(\d+)/', $page['url']))
    {
        $data['url'] = preg_replace('/\/\d+$/', '', $page['url']);
    }else{
        $data['url'] = $page['url'];
    }

    $data['publish_time'] = str_replace('发布于 ', '', $data['publish_time']);
    $data['view_count'] = str_replace('次浏览', '', $data['view_count']);
    $data['view_count'] = str_replace(',', '', $data['view_count']);
    return $data;
};
$spider->start();