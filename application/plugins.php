<?php

return function ($application) {
    if (!defined('APPATH')) {
        define('APPATH', __DIR__);
    }
    
    $container = $application->getContainer();
    $configuration = $container['settings'];

    if (isset($configuration['timezone'])) {
        if (strlen($configuration['timezone'])) {
            //  Set timezone
            date_default_timezone_set($configuration['timezone']);
        }
    }

    $application->add(new \Slim\Middleware\Session([
        'name'          => 'slimbase_session',
        'autorefresh'   => true,
        'lifetime'      => '12 hours'
    ]));

    //register session plugin
    $container['session'] = function ($container) {
        return new \SlimSession\Helper;
    };

    //register eloquent
    $container['database'] = \sys\Database::instance();
      
    //sitebase DI
    $container['sitebase'] = \sys\Sitebase::instance();

    //register view
    $view = new \sys\View(APPATH . '/view', [
        'cache' => false
    ]);

    $view->setContainer($container);

    $container['view'] = function ($container) use ($view) {
        $view->addExtension(new \Slim\Views\TwigExtension(
            $container->router,
            $container->request->getUri()
        ));
        $view->setTwig('baseUrl', $container->baseUrl);
        $view->addExtension(new Knlv\Slim\Views\TwigMessages(
            new Slim\Flash\Messages()
        ));
        $view->addExtension(new \Twig\Extension\DebugExtension());
        return $view;
    };

    //authentication
    $container['auth'] = function ($container) {
        return new \app\helper\AuthenticationHelper($container);
    };

    // Flash message
    $container['flash'] = function () {
        return new \Slim\Flash\Messages();
    };

    if (!$container['settings']['displayErrorDetails']) {
        $errorHandler = function ($c) {
            return function ($request, $response, $exception) use ($c) {
                return $c['view']->setResponse($response->withStatus(500))->render('error.twig');
            };
        };

        $container['errorHandler'] = $errorHandler;
        $container['phpErrorHandler'] = $errorHandler;

        $container['notFoundHandler'] = function ($c) {
            return function ($request, $response, $exception) use ($c) {
                return $c['view']->setResponse($response->withStatus(404))->render('notfound.twig');
            };
        };
    }
};
