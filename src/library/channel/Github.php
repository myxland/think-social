<?php

namespace myxland\social\library\channel;

use myxland\social\library\AccessToken;
use myxland\social\library\Channel;
use myxland\social\library\exception\Exception;
use myxland\social\library\User;

class Github extends Channel
{
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://github.com/login/oauth/authorize', $state);
    }

    protected function getTokenUrl()
    {
        return 'https://github.com/login/oauth/access_token';
    }

    protected function getAccessToken($code)
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers'     => ['Accept' => 'application/json'],
            'form_params' => $this->getTokenParams($code),
        ]);

        $body = json_decode($response->getBody(), true);
        if (isset($body['access_token'])) {
            return AccessToken::make($body);
        } else {
            throw new Exception($body['error_description']);
        }
    }

    protected function getUserByToken(AccessToken $token)
    {
        $userUrl  = 'https://api.github.com/user?access_token=' . $token->getToken();
        $response = $this->getHttpClient()->get($userUrl, $this->getRequestOptions());
        $user     = json_decode($response->getBody(), true);

        return $user;
    }

    /**
     * 创建User对象
     *
     * @param array $user
     * @return User
     */
    protected function makeUser(array $user)
    {
        return User::make($user, [
            'nickname' => 'login',
            'avatar'   => 'avatar_url',
        ]);
    }

    /**
     * 设置http请求参数
     *
     * @return array
     */
    protected function getRequestOptions()
    {
        return [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
            ],
        ];
    }
}