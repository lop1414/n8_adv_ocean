<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return 'hello';
});



// 后台公共权限接口
$router->group([
    'middleware' => ['center_login_auth', 'admin_request_log', 'access_control_allow_origin']
], function () use ($router) {

});

// 后台需授权接口
$router->group([
    'prefix' => 'admin',
    'middleware' => ['center_menu_auth', 'admin_request_log', 'access_control_allow_origin']
], function () use ($router) {

    // APP应用
    $router->group(['prefix' => 'app'], function () use ($router) {
        $router->post('select', 'Admin\AppController@select');
        $router->post('get', 'Admin\AppController@get');
        $router->post('read', 'Admin\AppController@read');
        $router->post('create', 'Admin\AppController@create');
        $router->post('update', 'Admin\AppController@update');
//        $router->post('enable', 'Admin\AppController@enable');
//        $router->post('disable', 'Admin\AppController@disable');
    });

    // 巨量
    $router->group(['prefix' => 'ocean'], function () use ($router) {

        // 账户
        $router->group(['prefix' => 'account'], function () use ($router) {
            $router->post('select', 'Admin\Ocean\AccountController@select');
            $router->post('get', 'Admin\Ocean\AccountController@get');
            $router->post('read', 'Admin\Ocean\AccountController@read');
//            $router->post('enable', 'Admin\Ocean\AccountController@enable');
//            $router->post('disable', 'Admin\Ocean\AccountController@disable');
            $router->post('update', 'Admin\Ocean\AccountController@update');

        });
    });
});


// 前台接口
$router->group([
    'prefix' => 'front',
    'middleware' => ['api_sign_valid', 'access_control_allow_origin']
], function () use ($router) {

});
