<?php

use think\facate\Hook;
use think\facate\Route;


\think\Console::addDefaultCommands([
    \myxland\social\library\SendConfig::class,
]);

function social_url($channel, $bind = false)
{
    if ($bind) {
        $route = 'SOCIAL_BIND';
    } else {
        $route = 'SOCIAL';
    }

    return url($route, ['channel' => $channel]);
}

Hook::add('app_init', function () {
    //注册路由
    if ($route = config('social.route')) {

        $controller = config('social.controller');

        Route::get([
            "SOCIAL_BIND_CALLBACK",
            "{$route}/:channel/callback/bind",
        ], $controller . '@handleSocialCallbackForBind');

        Route::get([
            "SOCIAL_CALLBACK",
            "{$route}/:channel/callback",
        ], $controller . '@handleSocialCallback');

        Route::get([
            "SOCIAL_BIND",
            "{$route}/:channel/bind",
        ], $controller . '@redirectToSocialForBind');

        Route::get([
            "SOCIAL",
            "{$route}/:channel",
        ], $controller . '@redirectToSocial');
    }
});