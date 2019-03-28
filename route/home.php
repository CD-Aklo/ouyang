<?php
Route::group(['namespace' => 'home','prefix' => ''],function (){
    Route::get('/','Index/index');
    Route::get('user/cart$','Cart/cartList');
    Route::post('login','User/login');
    Route::get('loginout','User/loginout');
});