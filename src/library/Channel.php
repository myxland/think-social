<?php

namespace myxland\social\library;

use Closure;
use GuzzleHttp\Client;
use think\helper\Str;
use think\facade\Session;
use myxland\social\library\exception\InvalidStateException;
use myxland\social\library\exception\UserCancelException;

abstract class Channel
{
    static protected $codeResolver;

    static protected $stateResolver;

    protected $stateless = false;

    /** @var  Client Http 客户端 */
    protected $httpClient;

    protected $encodingType = PHP_QUERY_RFC1738;

    protected $clientId;

    protected $clientSecret;

    protected $redirectUrl = null;

    protected $scopes = [];

    /** @var string scope 分隔符 */
    protected $scopeSeparator = ',';

    /** @var array 自定义参数 */
    protected $parameters = [];

    protected $accessToken = null;

    public function __construct($config)
    {

        if (! isset($config['client_id']) || ! isset($config['client_secret'])) {
            throw new \InvalidArgumentException("Config client_id,client_secret must be supply.");
        }

        $this->clientId     = $config['client_id'];
        $this->clientSecret = $config['client_secret'];
    }

    /**
     * 跳转到第三方平台登录
     */
    public function redirect()
    {
        $state = null;
        if ($this->usesState()) {
            Session::set('state', $state = $this->getState());
        }

        return redirect($this->getAuthUrl($state));
    }

    /**
     * Set redirect url.
     *
     * @param string $redirectUrl
     *
     * @return $this
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    /**
     * Return the redirect url.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * 设置token
     *
     * @param AccessToken $accessToken
     * @return $this
     */
    public function setAccessToken(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * 获取第三方平台登录成功后的用户
     *
     * @return User
     * @throws InvalidStateException
     */
    public function user()
    {
        if (! $this->accessToken) {
            if ($this->hasInvalidState()) {
                throw new InvalidStateException;
            }
            $this->accessToken = $this->getAccessToken(static::resolveCode());
        }

        $user = $this->makeUser($this->getUserByToken($this->accessToken));

        return $user->setToken($this->accessToken)->setChannel(strtolower(basename(str_replace('\\', '/', get_class($this)))));
    }

    /**
     * 设置scope
     *
     * @param  array $scopes
     * @return $this
     */
    public function scopes(array $scopes)
    {
        $this->scopes = $scopes;

        return $this;
    }

    /**
     * 设置额外参数
     *
     * @param array $parameters
     * @return $this
     */
    public function with(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * 获取scope
     *
     * @return array
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    public function stateless()
    {
        $this->stateless = true;

        return $this;
    }

    protected function usesState()
    {
        return ! $this->stateless;
    }

    protected function isStateless()
    {
        return $this->stateless;
    }

    protected function getState()
    {
        return Str::random(40);
    }

    public static function resolveCode()
    {
        if (isset(static::$codeResolver)) {
            return call_user_func(static::$codeResolver);
        }

        if (request()->has('code')) {
            return request()->param('code');
        }

        throw new UserCancelException();
    }

    public static function codeResolver(Closure $resolver)
    {
        static::$codeResolver = $resolver;
    }

    public static function stateResolver(Closure $resolver)
    {
        static::$stateResolver = $resolver;
    }

    public static function resolveState()
    {
        if (isset(static::$stateResolver)) {
            return call_user_func(static::$stateResolver);
        }

        return request()->param('state');
    }

    protected function hasInvalidState()
    {
        if ($this->isStateless()) {
            return false;
        }
        $state = Session::pull('state');

        return ! (strlen($state) > 0 && static::resolveState() === $state);
    }

    abstract protected function getAuthUrl($state);

    abstract protected function getTokenUrl();

    abstract protected function getUserByToken(AccessToken $token);

    /**
     * 创建User对象
     *
     * @param array $user
     * @return User
     */
    abstract protected function makeUser(array $user);

    protected function getAuthParams($state)
    {
        $fields = array_merge([
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUrl,
            'scope'         => $this->formatScopes($this->scopes, $this->scopeSeparator),
            'response_type' => 'code',
        ], $this->parameters);

        if ($this->usesState()) {
            $fields['state'] = $state;
        }

        return $fields;
    }

    /**
     * 格式化scope
     *
     * @param array $scopes
     * @param string $scopeSeparator
     * @return string
     */
    protected function formatScopes(array $scopes, $scopeSeparator)
    {
        return implode($scopeSeparator, $scopes);
    }

    /**
     * 创建认证跳转url
     *
     * @param $url
     * @param $state
     * @return string
     */
    protected function buildAuthUrlFromBase($url, $state)
    {
        return $url . '?' . http_build_query($this->getAuthParams($state), '', '&', $this->encodingType);
    }

    protected function getTokenParams($code)
    {
        return [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code'          => $code,
            'redirect_uri'  => $this->redirectUrl,
        ];
    }

    abstract protected function getAccessToken($code);

    /**
     * 获取http客户端实例
     *
     * @return Client
     */
    protected function getHttpClient()
    {
        if (is_null($this->httpClient)) {
            $this->httpClient = new Client();
        }

        return $this->httpClient;
    }
}
