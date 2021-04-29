<?php
namespace sys;

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

define('SYSPATH', __DIR__);
require '../vendor/autoload.php';

$configuration = Config::claim();

$configuration['settings']['baseUrl'] = isset($configuration['settings']['baseUrl']) ? $configuration['settings']['baseUrl'] : '';

$application    = new \Slim\App($configuration);
$container      = $application->getContainer();

Helper::$container = $container;
Controller::$container = $container;

$container['baseUrl'] = strlen($configuration['settings']['baseUrl']) <= 0 ? $container->request->getUri()->getBaseUrl() : $configuration['settings']['baseUrl'];

$container['settings']['appPlugins']( $application );
$container['settings']['appRoutes']( $application );

$application->run();
