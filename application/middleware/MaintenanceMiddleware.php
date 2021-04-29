<?php namespace app\middleware;

class MaintenanceMiddleware extends \sys\Middleware
{
    public function __invoke($req, $res, $next)
    {
        $settings = $this->container->settings;
        $maintenance = false;

        if ($req->getUri()->getPath() == '/maintenance') {
            return $next($req, $res);
        }

        if ($maintenance) {
            $iptables[] = '110.139.179.89';
            $iptables[] = '180.252.73.154';
            
            if (!in_array($req->getServerParam('REMOTE_ADDR'), $iptables)) {
                return $res->withRedirect($this->container->baseUrl . '/maintenance');
            }
        }
        
        return $next($req, $res);
    }
}
