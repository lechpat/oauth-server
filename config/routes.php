<?php
\Cake\Routing\Router::plugin('OAuthServer', ['path' => '/oauth'], function (\Cake\Routing\RouteBuilder $routes) {
    $routes->extensions(['json']);
    $routes->connect(
        '/**',
        [
            'controller' => 'OAuth',
            'action' => 'defaultResponse'
        ]
    );
    $routes->connect(
        '/',
        [
            'controller' => 'OAuth',
            'action' => 'oauth'
        ]
    );
    $routes->connect(
        '/authorize',
        [
            'controller' => 'OAuth',
            'action' => 'authorize'
        ]
    );
    $routes->connect(
        '/access_token',
        [
            'controller' => 'OAuth',
            'action' => 'accessToken'
        ]
    );
});
