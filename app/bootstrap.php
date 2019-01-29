<?php

// Load vendor autoloader
require __DIR__ . '/../vendor/autoload.php';

// Set configs
$config['displayErrorDetails'] = true;

// Create app
$app = new \Slim\App(['settings' => $config]);

// Get container
$container = $app->getContainer();

// Register components on container
$container['env'] = function($container) {
    $env = (object) parse_ini_file(__DIR__ . '/../env.ini', true);
    return $env;
};

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('../resources/templates', [
        'cache' => false,
        'debug' => true
    ]);

    // Instantiate and add Slim specific extension
    $router = $container->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));
    $view->addExtension(new \Twig_Extension_Debug());

    return $view;
};

$container['mailer'] = function($container) {
    $twig   = $container['view'];
    $env    = $container['env'];
    $mailer = new \Anddye\Mailer\Mailer($twig, [
        'host'      => $env->host,      // SMTP Host
        'port'      => $env->port,      // SMTP Port
        'username'  => $env->user,      // SMTP Username
        'password'  => $env->pass,      // SMTP Password
        'protocol'  => $env->protocol   // SSL or TLS
    ]);

    // Set the details of the default sender
    $mailer->setDefaultFrom($env->fromMail, $env->fromName);

    return $mailer;
};

$container['AjaxController'] = function ($container) {
    return new App\Controllers\AjaxController($container);
};

$container['MainController'] = function ($container) {
	return new App\Controllers\MainController($container);
};

// Load routes
require __DIR__ . '/routes.php';
