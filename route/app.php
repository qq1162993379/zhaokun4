<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

// 获取当前计数
Route::get('/api/count', 'index/getCount');

// 更新计数，自增或者清零
Route::post('/api/count', 'index/updateCount');

Route::get('/api/test', 'index/test');
Route::post('/api/test2', 'index/test2');
Route::post('/api/test_ins', 'index/test_ins');



//前端接口
Route::get('/index/userzt', 'home/userzt');
Route::get('/index/productlist', 'home/productlist');
Route::get('/index/user_price', 'home/user_price');
Route::get('/index/user_priceadd', 'home/user_priceadd');
Route::post('/index/user_priceupdate', 'home/user_priceupdate');
Route::post('/index/userinfoupdate', 'home/userinfoupdate');
Route::get('/index/userinfo', 'home/userinfo');
Route::post('/index/dingdan', 'home/dingdan');
Route::get('/index/dingdanget', 'home/dingdanget');
Route::post('/index/dingdanxq', 'home/dingdanxq');


//test
Route::post('/index/jsontest', 'home/jsontest');


//前台接口
Route::get('/qiantai/index', 'qiantai/index');
Route::get('/qiantai/admin', 'qiantai/admin');
Route::post('qiantai/login', 'qiantai/login');
Route::post('qiantai/dingdanget', 'qiantai/dingdanget');
Route::post('qiantai/dingdan_xq', 'qiantai/dingdan_xq');
Route::post('qiantai/dingdan_jieshou', 'qiantai/dingdan_jieshou');
Route::post('qiantai/dingdan_kequhuo', 'qiantai/dingdan_kequhuo');
Route::post('qiantai/dingdan_quhuo', 'qiantai/dingdan_quhuo');


//test

Route::post('/qiantai/addqiantai', 'qiantai/addqiantai');


//后端接口


Route::get('/admin/index', 'admin/index');
Route::get('/admin/admin', 'admin/admin');
Route::post('admin/login', 'admin/login');

    //订单相关
Route::post('admin/dingdanget', 'admin/dingdanget');
Route::post('admin/dingdan_xq', 'admin/dingdan_xq');
Route::post('admin/dingdan_jieshou', 'admin/dingdan_jieshou');
Route::post('admin/dingdan_kequhuo', 'admin/dingdan_kequhuo');
Route::post('admin/dingdan_quhuo', 'admin/dingdan_quhuo');
Route::post('admin/dingdan_jiezhang', 'admin/dingdan_jiezhang');
Route::post('admin/dingdan_tongji', 'admin/dingdan_tongji');
Route::post('admin/dingdan_jiezhang_pl', 'admin/dingdan_jiezhang_pl');


    //产品相关
Route::post('/admin/product_page', 'admin/product_page');
Route::get('/admin/productlist', 'admin/productlist');
Route::post('/admin/productadd', 'admin/productadd');
Route::post('/admin/productupdate', 'admin/productupdate');
Route::post('/admin/productpaixu', 'admin/productpaixu');
Route::post('/admin/productdel', 'admin/productdel');


Route::post('/admin/zhuwa_page', 'admin/zhuwa_page');
Route::post('/admin/zhuwa_list', 'admin/zhuwa_list');
Route::post('/admin/zhuwa_chicun_add', 'admin/zhuwa_chicun_add');
Route::post('/admin/zhuwa_chicun_del', 'admin/zhuwa_chicun_del');

    //规格
Route::post('/admin/guige_page', 'admin/guige_page');
Route::post('/admin/guige_list', 'admin/guige_list');
Route::post('/admin/zhuwa_guige_add', 'admin/zhuwa_guige_add');
Route::post('/admin/zhuwa_guige_del', 'admin/zhuwa_guige_del');
Route::post('/admin/zhuwa_guige_update', 'admin/zhuwa_guige_update');
    //厚度
Route::post('/admin/houdu_page', 'admin/houdu_page');
Route::post('/admin/houdu_list', 'admin/houdu_list');
Route::post('/admin/zhuwa_houdu_add', 'admin/zhuwa_houdu_add');
Route::post('/admin/zhuwa_houdu_del', 'admin/zhuwa_houdu_del');
Route::post('/admin/zhuwa_houdu_update', 'admin/zhuwa_houdu_update');   
    //种类
Route::post('/admin/zhonglei_page', 'admin/zhonglei_page');
Route::post('/admin/zhonglei_list', 'admin/zhonglei_list');
Route::post('/admin/zhuwa_zhonglei_add', 'admin/zhuwa_zhonglei_add');
Route::post('/admin/zhuwa_zhonglei_del', 'admin/zhuwa_zhonglei_del');
Route::post('/admin/zhuwa_zhonglei_update', 'admin/zhuwa_zhonglei_update');

    //价格
Route::post('/admin/product_price_list', 'admin/product_price_list');
Route::post('/admin/product_price_page', 'admin/product_price_page');
Route::post('/admin/product_price_option', 'admin/product_price_option');
Route::post('/admin/product_prict_add', 'admin/product_prict_add');
Route::post('/admin/product_prict_del', 'admin/product_prict_del');
Route::post('/admin/product_prict_update', 'admin/product_prict_update');

    //订单修改明细
Route::post('/admin/dd_xiugai_jl', 'admin/dd_xiugai_jl');



    

