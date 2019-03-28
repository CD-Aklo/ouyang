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

Route::group(['domain' => 'tyspn.com'],function (){
    include base_path().'/route/home.php';
});
Route::group(['domain' => 'manager.tyspn.com'],function (){
    include  base_path()."/route/manager.php";
});

//Route::get('/','home/Index/index');
//Route::miss('home/Index/miss');
Route::rule('login','admin/Login/index');
//Route::get('hello/:name', 'index/hello');
////购物车相关路由
//Route::get('user/cart$','home/Cart/cartList');
//Route::get('cart/add$','home/Cart/add');
//Route::post('cart/del$','home/Cart/del');
//Route::post('cart/order$','home/Cart/order');
////订单相关路由
//Route::get('user/order$','home/Order/orderList');
//Route::get('order/detail$','home/Order/detail');
//Route::post('order/pay$','home/Order/pay');
//Route::get('order/comment$','home/Order/commentList');
//Route::post('order/comment$','home/Order/comment');
////预售提货
//Route::post('order/pickup$','home/Order/pickup');

Route::rule('login','admin/Login/index');
//Route::post('other/kk','admin/other/friendStatus');
//Route::get('/','home/Index/index');
//Route::miss('home/Index/miss');
//Route::get('hello/:name', 'index/hello');
////购物车相关路由
//Route::get('user/cart$','home/Cart/cartList');
//Route::get('user/info$','home/Cart/cartInfo');
//Route::get('cart/add$','home/Cart/add');
//Route::post('cart/del$','home/Cart/del');
//Route::post('cart/order$','home/Cart/order');
////订单相关路由
//Route::get('user/order$','home/Order/orderList');
//Route::get('order/detail$','home/Order/detail');
//Route::post('order/pay$','home/Order/pay');
//Route::get('order/comment$','home/Order/commentList');
//Route::post('order/comment$','home/Order/comment');
////预售提货
//Route::post('order/pickup$','home/Order/pickup')

