<?php

/****************************************
 * 互联登录的路由，包括 github, QQ， 微博 登录
 ****************************************/
Auth::routes();

Route::get('init', function (\Illuminate\Http\Request $request) {

    $number = $request->input('number', 20);

    dd(Redis::connection()->lpush('seckill:', array_fill(0, $number, 9)));
});

Route::get('test', function () {


    var_dump(Redis::connection()->lpop('seckill:'));
});


Route::namespace('Auth')->group(function(){

    // 获取验证码
    Route::get('captcha', 'RegisterController@captcha');

    /****************************************
     * 1. 激活账号的路由
     * 2. 重新发送激活链接的路由
     ****************************************/
    Route::get('register/active/{token}', 'UserController@activeAccount');
    // again send active link
    Route::get('register/again/send/{id}', 'UserController@sendActiveMail');

    /****************************************
     * 互联登录的路由，包括 github, QQ， 微博 登录
     ****************************************/
    Route::get('auth/oauth', 'AuthLoginController@redirectToAuth');
    Route::get('auth/oauth/callback', 'AuthLoginController@handleCallback');
    Route::get('/auth/oauth/unbind', 'AuthLoginController@unBind');
});

/****************************************
 * 主页相关的路由
 ****************************************/
Route::get('/', 'HomeController@index');



/****************************************
 * 1. 通过商品拼音首字母得到商品列表
 * 2. 搜索商品
 ****************************************/
Route::get('products/pinyin/{pinyin}', 'ProductController@getProductsByPinyin');
Route::get('products/search', 'ProductController@search');

/****************************************
 * 1. 商品分类的资源路由，
 * 2. 商品的资源路由哦
 * 3. 购物车的资源路由
 ****************************************/
Route::resource('categories', 'CategoryController', ['only' => ['index', 'show']]);
Route::resource('products', 'ProductController', ['only' => ['index', 'show']]);
Route::resource('cars', 'CarController');
// 秒杀商品
Route::get('seckills/{id}', 'SeckillController@show');

/****************************************
 * 用户相关的路由
 ****************************************/
Route::middleware('user.auth')->prefix('user')->namespace('User')->group(function(){

    /****************************************
     * 1. 用户的个人中心
     * 2. 订阅星期一商城以获取新闻
     * 3. 取消订阅
     ****************************************/
    Route::get('/', 'UserController@index');
    Route::post('subscribe', 'UserController@subscribe');
    Route::post('desubscribe', 'UserController@deSubscribe');

    /****************************************
     * 1. 修改密码页面
     * 2. 修改密码
     ****************************************/
    Route::get('password', 'UserController@showPasswordForm');
    Route::post('password', 'UserController@updatePassword');

    /****************************************
     * 1. 用户设置个人中心
     * 2. 修改用户头像
     * 3. 修改用户资料
     ****************************************/
    Route::get('setting', 'UserController@setting');
    Route::post('upload/avatar', 'UserController@uploadAvatar');
    Route::put('update', 'UserController@update');

    /****************************************
     * 1. 设置用户的默认收货地址
     * 2. 根据省份 id 获取城市列表
     * 3. 收货地址的资源路由
     ****************************************/
    Route::post('addresses/default/{address}', 'AddressController@setDefaultAddress');
    Route::get('addresses/cities', 'AddressController@getCities');
    Route::resource('addresses', 'AddressController');

    /****************************************
     * 1. 用户收藏商品的列表
     * 2. 收藏，取消收藏商品
     * 3. 单个订单的下单
     * 4. 订单的资源路由
     ****************************************/
    Route::get('likes', 'LikesController@index');
    Route::put('likes/{id}', 'LikesController@toggle');
    Route::post('orders/single', 'OrderController@single');
    Route::resource('orders', 'OrderController')->only('index', 'show', 'destroy');

    // 评论商品
    Route::post('comments', 'CommentCOntroller@store');

    /****************************************
     * 1. 订单的创建（包括直接下单和购物车下单
     * 2. 退款接口
     * 3. 忘记付款了，再次付款
     ****************************************/
    Route::post('pay/store', 'PaymentController@store');
    Route::get('pay/orders/{order}/refund', 'RefundController@store');
    Route::get('pay/orders/{order}/again', 'PaymentController@againStore');
});

// 支付通知的接口
Route::get('pay/return', 'PaymentNotificationController@payReturn');
Route::post('pay/notify', 'PaymentNotificationController@payNotify');
