Thinkphp5.1 社会化登陆
======

### 安装
~~~
composer require myxland/think-social:dev-master
php think social:config
~~~

### 用法
1、控制器
~~~
<?php

namespace app\index\controller;

use think\Controller;

use myxland\social\Social;

class Auth extends Controller
{
    public function redirectToSocial($channel)
    {
        return Social::channel($channel)->redirect();
    }

    public function handleSocialCallback($channel)
    {
        $user = Social::channel($channel)->user();

        // $user->getToken();
        // $user->getId();
        // $user->getName();
        // $user->getNickname();
        // $user->getAvatar();
        // $user->getEmail();
    }
}
~~~
2、定义路由
~~~
Route::get('auth/:channel/callback', 'Auth/handleSocialCallback');
Route::get('auth/:channel', 'Auth/redirectToSocial');
~~~