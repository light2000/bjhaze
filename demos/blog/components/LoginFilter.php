<?php
use BJHaze\Behavior\BehaviorInterface;

class LoginFilter implements BehaviorInterface
{

    protected $user;

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function handle(&$action, array &$parameters, array &$before, array &$after, &$result)
    {
        if (! $this->user->isLogin())
            throw new RuntimeException(sprintf("guest can not pass action %s", $action), 403);
    }
}