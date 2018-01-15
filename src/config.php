<?php

/**
 * 社会化登陆配置信息
 */
return [
    /**
     * 可用频道
     */
    'channels'     => [
        /** QQ */
        'qq'     => [
            'client_id'     => '',
            'client_secret' => '',
        ],
        /** 微博 */
        'weibo'  => [
            'client_id'     => '',
            'client_secret' => '',
        ],
        /** GitHub */
        'github' => [
            'client_id'     => '',
            'client_secret' => '',
        ],
        /** 微信 */
        'wechat' => [
            'client_id'     => '',
            'client_secret' => '',
        ],
    ],
    /**
     * 自动路由
     */
    'route'        => false,
    'controller'   => \myxland\social\library\Controller::class,
    /**
     * 自动检测用户
     */
    'user_checker' => null,
    'redirect'     => [
        'bind'     => '/',
        'register' => '/',
        'complete' => '/',
    ],
];