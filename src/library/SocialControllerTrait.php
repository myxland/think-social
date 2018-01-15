<?php

namespace myxland\social\library;

use think\facade\Session;
use myxland\social\Social;

trait SocialControllerTrait
{
    protected function buildChannel($channel)
    {
        return Social::channel($channel);
    }

    protected function setRedirectUrl(Channel $social, $channel, $bind = false)
    {
        if (method_exists($this, 'getRedirectUrl')) {
            $social->setRedirectUrl($this->getRedirectUrl($channel, $bind));
        } else {
            if ($bind) {
                $route = 'SOCIAL_BIND_CALLBACK';
            } else {
                $route = 'SOCIAL_CALLBACK';
            }
            $redirectUrl = url($route, ['channel' => $channel], '', true);

            $social->setRedirectUrl($redirectUrl);
        }
    }

    public function redirectToSocial($channel, $bind = false)
    {
        $social = $this->buildChannel($channel);

        $this->setRedirectUrl($social, $channel, $bind);

        if (property_exists($this, 'scopes')) {
            $social->scopes($this->scopes);
        }

        if (method_exists($this, 'beforeRedirect')) {
            $this->beforeRedirect($social);
        }

        return $social->redirect();
    }

    public function redirectToSocialForBind($channel)
    {
        return $this->redirectToSocial($channel, true);
    }

    public function handleSocialCallback($channel)
    {
        $social = $this->buildChannel($channel);
        $this->setRedirectUrl($social, $channel);
        $user = $social->user();

        $checker = config('social.user_checker');
        if ($checker && is_subclass_of($checker, UserCheckerInterface::class)) {
            if ($checker::checkSocialUser($user)) {
                return redirect(config('social.redirect')['complete'])->restore();
            }
        }
        Session::flash('social_user', $user);

        return redirect(config('social.redirect')['register']);
    }

    public function handleSocialCallbackForBind($channel)
    {
        $social = $this->buildChannel($channel);
        $this->setRedirectUrl($social, $channel, true);
        $user = $social->user();
        Session::flash('social_user', $user);

        return redirect(config('social.redirect')['bind']);
    }
}