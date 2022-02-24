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
    $router->group(['prefix' => 'index'], function () use ($router) {
        // 投放监控
        $router->post('ad_dashboard', 'Admin\IndexController@adDashboard');
    });

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
        $router->post('open', '\\App\Common\Controllers\Admin\TaskController@open');
        $router->post('close', '\\App\Common\Controllers\Admin\TaskController@close');
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

        // 巨量计划更新
        $router->group(['prefix' => 'ocean_ad_update'], function () use ($router) {
            $router->post('select', 'Admin\SubTask\TaskOceanAdUpdateController@select');
            $router->post('read', 'Admin\SubTask\TaskOceanAdUpdateController@read');
            $router->post('create', 'Admin\SubTask\TaskOceanAdUpdateController@create');
        });
    });

    // 回传策略
    $router->group(['prefix' => 'convert_callback_strategy'], function () use ($router) {
        $router->post('create', '\\App\Common\Controllers\Admin\ConvertCallbackStrategyController@create');
        $router->post('update', '\\App\Common\Controllers\Admin\ConvertCallbackStrategyController@update');
        $router->post('select', '\\App\Common\Controllers\Admin\ConvertCallbackStrategyController@select');
        $router->post('get', '\\App\Common\Controllers\Admin\ConvertCallbackStrategyController@get');
        $router->post('read', '\\App\Common\Controllers\Admin\ConvertCallbackStrategyController@read');
    });

    // 回传策略组
    $router->group(['prefix' => 'convert_callback_strategy_group'], function () use ($router) {
        $router->post('create', '\\App\Common\Controllers\Admin\ConvertCallbackStrategyGroupController@create');
        $router->post('update', '\\App\Common\Controllers\Admin\ConvertCallbackStrategyGroupController@update');
        $router->post('select', '\\App\Common\Controllers\Admin\ConvertCallbackStrategyGroupController@select');
        $router->post('get', '\\App\Common\Controllers\Admin\ConvertCallbackStrategyGroupController@get');
        $router->post('read', '\\App\Common\Controllers\Admin\ConvertCallbackStrategyGroupController@read');
    });

    // 渠道-计划
    $router->group(['prefix' => 'channel_ad'], function () use ($router) {
        $router->post('update', 'Admin\ChannelAdController@update');
        $router->post('read', 'Admin\ChannelAdController@read');
    });

    // 转化回传
    $router->group(['prefix' => 'convert_callback'], function () use ($router) {
        $router->post('callback', '\\App\Common\Controllers\Admin\ConvertCallbackController@callback');
    });

    // 自定义转化回传
    $router->group(['prefix' => 'custom_convert_callback'], function () use ($router) {
        $router->post('create', '\\App\Common\Controllers\Admin\CustomConvertCallbackController@create');
    });

    // 点击
    $router->group(['prefix' => 'click'], function () use ($router) {
        $router->post('select', 'Admin\ClickController@select');
        $router->post('callback', 'Admin\ClickController@callback');
        $router->post('asset_event_callback', 'Admin\ClickController@assetEventCallback');
    });

    // 巨量
    $router->group(['prefix' => 'ocean'], function () use ($router) {
        // 账户
        $router->group(['prefix' => 'account'], function () use ($router) {
            $router->post('select', 'Admin\Ocean\AccountController@select');
            $router->post('get', 'Admin\Ocean\AccountController@get');
            $router->post('read', 'Admin\Ocean\AccountController@read');
            $router->post('update', 'Admin\Ocean\AccountController@update');
            $router->post('enable', 'Admin\Ocean\AccountController@enable');
            $router->post('disable', 'Admin\Ocean\AccountController@disable');
            $router->post('delete', 'Admin\Ocean\AccountController@delete');
            $router->post('batch_enable', 'Admin\Ocean\AccountController@batchEnable');
            $router->post('batch_disable', 'Admin\Ocean\AccountController@batchDisable');
            $router->post('batch_update_admin', 'Admin\Ocean\AccountController@batchUpdateAdmin');
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
        });

        // 广告计划
        $router->group(['prefix' => 'ad'], function () use ($router) {
            $router->post('select', 'Admin\Ocean\AdController@select');
            $router->post('read', 'Admin\Ocean\AdController@read');
        });

        // 广告创意
        $router->group(['prefix' => 'creative'], function () use ($router) {
            $router->post('select', 'Admin\Ocean\CreativeController@select');
            $router->post('read', 'Admin\Ocean\CreativeController@read');
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
            $router->post('tree', 'Admin\Ocean\CityController@tree');
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

        // 计划扩展
        $router->group(['prefix' => 'ad_extend'], function () use ($router) {
            $router->post('create', 'Admin\Ocean\AdExtendController@create');
            $router->post('update', 'Admin\Ocean\AdExtendController@update');
            $router->post('select', 'Admin\Ocean\AdExtendController@select');
            $router->post('read', 'Admin\Ocean\AdExtendController@read');
            $router->post('batch_update', 'Admin\Ocean\AdExtendController@batchUpdate');
        });

        // 创意组模板
        $router->group(['prefix' => 'creative_group_templete'], function () use ($router) {
            $router->post('create', 'Admin\Ocean\CreativeGroupTempleteController@create');
            $router->post('update', 'Admin\Ocean\CreativeGroupTempleteController@update');
            $router->post('select', 'Admin\Ocean\CreativeGroupTempleteController@select');
            $router->post('read', 'Admin\Ocean\CreativeGroupTempleteController@read');
            $router->post('delete', 'Admin\Ocean\CreativeGroupTempleteController@delete');
        });
    });
});


// 前台接口
$router->group([
    'prefix' => 'front',
    'middleware' => ['api_sign_valid', 'access_control_allow_origin']
], function () use ($router) {
    // 转化
    $router->group(['prefix' => 'convert'], function () use ($router) {
        $router->post('match', '\\App\Common\Controllers\Front\ConvertController@match');
    });

    // 转化回传
    $router->group(['prefix' => 'convert_callback'], function () use ($router) {
        $router->post('get', '\\App\Common\Controllers\Front\ConvertCallbackController@get');
    });

    // 渠道-计划
    $router->group(['prefix' => 'channel_ad'], function () use ($router) {
        $router->post('select', 'Front\ChannelAdController@select');
        $router->post('batch_update', 'Front\ChannelAdController@batchUpdate');
    });
});

$router->group(['middleware' => ['access_control_allow_origin']], function () use ($router) {
    // 点击
    $router->get('front/click', 'Front\AdvClickController@index');
});

// 巨量
$router->post('front/ocean/spi', 'Front\Ocean\IndexController@spi');
$router->get('front/ocean/grant', 'Front\Ocean\IndexController@grant');

// 测试
$router->post('front/ocean/lop', 'Front\Ocean\IndexController@test');
$router->post('test', 'TestController@test');

// 素材统计
$router->post('front/ocean/material_stat/get', 'Front\Ocean\MaterialStatController@get');
$router->post('front/ocean/material_stat/newest', 'Front\Ocean\MaterialStatController@newest');
$router->post('front/ocean/material_stat/protect', 'Front\Ocean\MaterialStatController@protect');
