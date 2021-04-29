<?php

use \app\controller\Frontpage;
use \app\controller\Api;
use \app\controller\Credential;

return function ($application) {
    $mvc = new \sys\MVC($application);

    $application->get('/', $mvc)
    ->add(new \app\middleware\AuthenticationMiddleware($application->getContainer()))
    ->add(new \app\middleware\MaintenanceMiddleware($application->getContainer()));
    // Hilangkan middleware diatas jika root url perlu terus tampil (tidak pake authentication)

    $application->group('', function () use ($mvc) {
        $this->map([
            'GET' ,
            'POST' ,
            'DELETE' ,
            'PUT' ,
            'PATCH' ,
            'HEAD' ,
            'OPTIONS'
        ], '/{controller}[/{params:.*}]', $mvc);
    })
    ->add(new \app\middleware\AuthenticationMiddleware($application->getContainer()))
    ->add(new \app\middleware\MaintenanceMiddleware($application->getContainer()));
};
