<?php

namespace myxland\social\library;

interface UserCheckerInterface
{
    public static function checkSocialUser(User $user, $autoLogin = true);
}