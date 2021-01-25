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
        //$router->post('enable', 'Admin\AppController@enable');
        //$router->post('disable', 'Admin\AppController@disable');
    });

    // 任务
    $router->group(['prefix' => 'task'], function () use ($router) {
        $router->post('select', '\\App\Common\Controllers\Admin\TaskController@select');
    });

    // 子任务
    $router->group(['prefix' => 'sub_task'], function () use ($router) {
        // 巨量视频上传
        $router->group(['prefix' => 'ocean_video_upload'], function () use ($router) {
            $router->post('select', 'Admin\SubTask\TaskOceanVideoUploadController@select');
            $router->post('read', 'Admin\SubTask\TaskOceanVideoUploadController@read');
        });

        // 巨量图片上传
        $router->group(['prefix' => 'ocean_image_upload'], function () use ($router) {
            $router->post('select', 'Admin\SubTask\TaskOceanImageUploadController@select');
            $router->post('read', 'Admin\SubTask\TaskOceanImageUploadController@read');
        });

        // 巨量同步
        $router->group(['prefix' => 'ocean_sync'], function () use ($router) {
            $router->post('select', 'Admin\SubTask\TaskOceanSyncController@select');
            $router->post('read', 'Admin\SubTask\TaskOceanSyncController@read');
        });

        // 巨量计划创意创建
        $router->group(['prefix' => 'ocean_ad_creative_create'], function () use ($router) {
            $router->post('select', 'Admin\SubTask\TaskOceanAdCreativeCreateController@select');
            $router->post('read', 'Admin\SubTask\TaskOceanAdCreativeCreateController@read');
        });
    });

    // 巨量
    $router->group(['prefix' => 'ocean'], function () use ($router) {
        // 账户
        $router->group(['prefix' => 'account'], function () use ($router) {
            $router->post('select', 'Admin\Ocean\AccountController@select');
            $router->post('get', 'Admin\Ocean\AccountController@get');
            $router->post('read', 'Admin\Ocean\AccountController@read');
            $router->post('update', 'Admin\Ocean\AccountController@update');
            //$router->post('enable', 'Admin\Ocean\AccountController@enable');
            //$router->post('disable', 'Admin\Ocean\AccountController@disable');
        });

        // 视频
        $router->group(['prefix' => 'video'], function () use ($router) {
            $router->post('upload', 'Admin\Ocean\VideoController@upload');
            $router->post('batch_upload', 'Admin\Ocean\VideoController@batchUpload');
        });

        // 图片
        $router->group(['prefix' => 'image'], function () use ($router) {
            $router->post('upload', 'Admin\Ocean\ImageController@upload');
            $router->post('batch_upload', 'Admin\Ocean\ImageController@batchUpload');
        });

        // 广告组
        $router->group(['prefix' => 'campaign'], function () use ($router) {
            $router->post('select', 'Admin\Ocean\CampaignController@select');
            $router->post('get', 'Admin\Ocean\CampaignController@get');
            $router->post('read', 'Admin\Ocean\CampaignController@read');
            $router->post('create', 'Admin\Ocean\CampaignController@create');
        });

        // 广告计划
        $router->group(['prefix' => 'ad'], function () use ($router) {
            $router->post('select', 'Admin\Ocean\AdController@select');
            $router->post('read', 'Admin\Ocean\AdController@read');
        });

        // 转化目标
        $router->group(['prefix' => 'ad_convert'], function () use ($router) {
            $router->post('select', 'Admin\Ocean\AdConvertController@select');
            $router->post('get', 'Admin\Ocean\AdConvertController@get');
            $router->post('read', 'Admin\Ocean\AdConvertController@read');
        });

        // 城市
        $router->group(['prefix' => 'city'], function () use ($router) {
            $router->post('get', 'Admin\Ocean\CityController@get');
        });

        // 商圈
        $router->group(['prefix' => 'region'], function () use ($router) {
            $router->post('get', 'Admin\Ocean\RegionController@get');
        });

        // 行业
        $router->group(['prefix' => 'industry'], function () use ($router) {
            $router->post('get', 'Admin\Ocean\IndustryController@get');
            $router->post('tree', 'Admin\Ocean\IndustryController@tree');
        });

        // 工具
        $router->group(['prefix' => 'tool'], function () use ($router) {
            $router->post('forward', 'Admin\Ocean\ToolController@forward');
            $router->post('sync', 'Admin\Ocean\OceanController@sync');
            $router->post('batch_create_ad_creative', 'Admin\Ocean\ToolController@batchCreateAdCreative');
        });

        // 定向模板
        $router->group(['prefix' => 'audience_templete'], function () use ($router) {
            $router->post('create', 'Admin\Ocean\AudienceTempleteController@create');
            $router->post('update', 'Admin\Ocean\AudienceTempleteController@update');
            $router->post('select', 'Admin\Ocean\AudienceTempleteController@select');
            $router->post('read', 'Admin\Ocean\AudienceTempleteController@read');
            $router->post('delete', 'Admin\Ocean\AudienceTempleteController@delete');
        });
    });
});


// 前台接口
$router->group([
    'prefix' => 'front',
    'middleware' => ['api_sign_valid', 'access_control_allow_origin']
], function () use ($router) {

});
