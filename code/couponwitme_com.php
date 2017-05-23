<?php
ini_set('memory_limit', '1024M');
require dirname(dirname(__FILE__)). '/core/init.php';
//建表
$date = date('Ymd');
$stores_table = 'r_stores';
$promos_table = 'r_promos';


$configs = array(
    'name' => 'couponwitme',
    'log_show' => false,
    'log_type' => 'error,debug',
    'tasknum' => 8,
    'interval' => 1500,
    'max_try' => 5,
    'export' => array(
    	'type' => 'db',
    	'table' => $stores_table,
    ),
    'domains' => array(
    	'couponwitme.com',
    	'www.couponwitme.com',
    ),
    'scan_urls' => array(
    	'http://www.couponwitme.com/stores/',
    ),
    'list_url_regexes' => array(
    	'http://www.couponwitme.com/stores/[A-Z]/',	
    ),
    'content_url_regexes' => array(
    	'http://www.couponwitme.com/vouchers/.*/',		
    ),
    'fields' => array(
    	array(
    		'name' => 'name',
    		'selector' => '//div[@id="right"]//h1',
    		'required' => true,
    	),
    	array(
    		'name' => 'destination_url',
    		'selector' => '//div[@id="store_screen"]/a/@href',
    		'required' => true,
    	),
    	array(
    		'name' => 'image',
    		'selector' => '//div[@id="store_screen"]/a/img/@src',
    		'required' => true,
    	),
    	array(
    		'name' => 'meta_title',
    		'selector' => '/html/head/title',
    		'required' => true,
    	),
    	array(
    		'name' => 'coupon_list',
    		'selector' => '//div[@id="coupon_list"]',
    		'children' => array(
    			array(
                    'name' => 'coupon_block',
                    'selector' => '//div[contains(@class, "coupon_block")]',
                    'repeated' => true,
                    'children' => array(
                        array(
                            'name' => 'out_id',
                            'selector' => '//p[contains(@class, "coupon_title")]/a/@infos',
                        ),
                        array(
                            'name' => 'title',
                            'selector' => '//p[contains(@class, "coupon_title")]/a',
                        ),
                        array(
                            'name' => 'description',
                            'selector' => '//span[contains(@class, "cpdesc")]',
                        ),
                        array(
                            'name' => 'code',
                            'selector' => '//span[contains(@class, "coupon_code")]',
                        ),
                        array(
                            'name' => 'destination_url',
                            'selector' => '//p[contains(@class, "coupon_title")]/a/@href',
                        ),
                        array(
                            'name' => 'expired_at',
                            'selector' => '//div[contains(@class, "coupon_infor")]/p[last()]',
                        ),
                        array(
                            'name' => 'clickcnt',
                            'selector' => '//div[contains(@class, "others")]/div[contains(@class, "click")]',
                        ),
                    ),
                ),
  			),
    	),
    ),
);
$spider = new phpspider($configs);
$spider->on_content_page = function($page, $content, $phpspider) 
{
    return false;
};
$spider->on_extract_field = function($fieldname, $data, $page) 
{
	if($fieldname == 'expired_at' && !empty($fieldname))
    {
        $expired_time = str_replace('Expires: ', '', $data);
        if(strtolower($expired_time) == 'soon')
        {
            $expired_time = '0000-00-00 00:00:00';
        }else{
            $expired_time = date('Y-m-d H:i:s', strtotime('+ '.$expired_time));
        }
        return $expired_time;
    }elseif($fieldname == 'out_id')
    {
        $tmp = explode('_', $data);
        if(isset($tmp[0]))
            return $tmp[0];
    }elseif($fieldname == 'clickcnt')
    {
        $tmp = explode(' ', $data);
        if(isset($tmp[0]))
            return $tmp[0];
    }
};
$spider->on_extract_page = function($page, $data)
{
    global $promos_table;
    $data['out_site'] = 'couponwitme.com';
    $data['created_at'] = date('Y-m-d H:i:s');
    $data['updated_at'] = date('Y-m-d H:i:s');
    if(isset($data['coupon_list']))
    {
        $coupon_list = $data['coupon_list'];
        //存入coupon, 分析所有的couponid+site,看是否存在，存在则更新，不存在则插入
        if(isset($coupon_list['coupon_block']))
        {
            $coupons = $coupon_list['coupon_block'];
            foreach ($coupons as $k => $v) {
                $coupons[$k]['store_name'] = $data['name'];
                $coupons[$k]['area'] = 'uk';
                $coupons[$k]['start_at'] = date('Y-m-d H:i:s');
                $coupons[$k]['created_at'] = date('Y-m-d H:i:s');
                $coupons[$k]['updated_at'] = date('Y-m-d H:i:s');
            }
            db::insert_batch($promos_table, $coupons);
        }
        unset($data['coupon_list']);
    }
    return $data;
};


$spider->start();