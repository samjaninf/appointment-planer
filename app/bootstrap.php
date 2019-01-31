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
    $env = $container['env'];
    $mailer = new \PHPMailer\PHPMailer\PHPMailer;
    $mailer->SMTPDebug  = 2;
    $mailer->isSMTP();
    $mailer->CharSet    = 'UTF-8';
    $mailer->Host       = $env->host;
    $mailer->SMTPAuth   = true;
    $mailer->Username   = $env->user;
    $mailer->Password   = $env->pass;
    $mailer->SMTPSecure = $env->protocol;
    $mailer->Port       = 465;
    $mailer->setFrom($env->fromMail, $env->fromName);

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
