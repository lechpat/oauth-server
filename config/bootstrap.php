<?php
use Cake\Event\EventManager;
use OAuthServer\Middleware\OAuthServerMiddleware;

EventManager::instance()->on(
    'Server.buildMiddleware',
    function ($event, $middleware) {
       $middleware->add(new OAuthServerMiddleware());
    });


