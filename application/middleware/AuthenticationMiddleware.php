<?php namespace app\middleware;

use \app\model\MenuModel;

class AuthenticationMiddleware extends \sys\Middleware
{
    public function __invoke($req, $res, $next)
    {
        $auth = $this->container->auth;

        $exception = [
            '/authentication',
            '/authentication/signin' ,
            '/authentication/submit-reset',
            '/authentication/reset',
            '/authentication/usernameExist',
            '/maintenance'
        ];

        $inception = [
            '/authentication'
        ];

        $uripath = $req->getUri()->getPath();

        $uripath = substr($uripath, 0, 1) != '/' ? '/'. $uripath : $uripath;

        if (!in_array($uripath, $exception)) {
            if (!$auth->recognized()) { // tambahkan kondisi && $uripath != '/' supaya root tidak kena redirect
                return $res->withRedirect($this->container->baseUrl . '/authentication?redirected=true');
            }

            $allowedControllers = MenuModel::with('groupPermission')
                    ->where('path', '<>', null)
                    ->whereHas('groupPermission', function ($query) {
                        $query->where('groupId', $this->container->session->user['groupId']);
                    })->pluck('path')->toArray();
            $allowedControllers[] = 'authentication';
            $allowedControllers[] = 'profile';
            $allowedControllers[] = 'tools';
            $allowedControllers[] = 'home';
            if (!in_array(explode('/', $uripath)[1], $allowedControllers) && $uripath != '/') {
                throw new \Slim\Exception\NotFoundException($req, $res);
            }
        } else {
            if (in_array($uripath, $inception) && $auth->recognized()) {
                return $res->withRedirect($this->container->baseUrl . '/');
            }
        }


        return $next($req, $res);
    }
}
