<?php namespace sys;

class MVC
{
    protected $app;

    private $controllerNamespace = '\app\controller';
    private $controllerDefault  = '\Home';
    private $actionDefault      = 'Index';
    private $argsDefault        = [];

    public static $action;
    public static $controller;
    public static $arguments = [];
    public static $baseUrl;
    public static $settings;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function __invoke($req, $res, $args)
    {
        $app = $this->app;

        $defaultController  = $this->controllerNamespace . $this->controllerDefault;
        $defaultAction      = $this->actionDefault;
        $defaultArguments   = $this->argsDefault;
    
        $container = $app->getContainer();
    
        static::$settings = $container['settings'];
        static::$baseUrl = $container->baseUrl;

        if ($req->getUri()->getPath() == "/") {
            if (!class_exists($defaultController)) {
                throw new \Slim\Exception\NotFoundException($req, $res);
            }

            static::$controller = $defaultController;
            static::$action     = $defaultAction;

            $container['mvc'] = function ($container) use ($defaultController, $defaultAction, $defaultArguments) {
                $obj = new \stdClass;
                $obj->controller = $defaultController;
                $obj->action = $defaultAction;
                $obj->arguments = $defaultArguments;
                return $obj;
            };

            return $defaultController::instance($container)->{$defaultAction}();
        } else {
            $controllerName = $this->normalizeSlug($args['controller']);
    
            $params = isset($args['params']) ? explode('/', $args['params']) : [];
    
            $actionName     = count($params) > 0 ? array_shift($params) : $defaultAction;
            $actionName     = $this->normalizeSlug($actionName);
    
            $ctrl = $this->controllerNamespace ."\\". $controllerName;
            
            if (class_exists($ctrl) && strtolower($controllerName) != 'base') {
                static::$controller = $controllerName;
                static::$action     = $actionName;
                static::$arguments  = $params;

                $container['mvc'] = function ($container) use ($controllerName, $actionName, $params) {
                    $obj = new \stdClass;
                    $obj->controller = $controllerName;
                    $obj->action = $actionName;
                    $obj->arguments = $params;
                    return $obj;
                };

                $controller = $ctrl::instance($container);
    
                if (method_exists($controller, $actionName)) {
                    return call_user_func_array([ $controller , $actionName ], $params);
                }
            }
    
            throw new \Slim\Exception\NotFoundException($req, $res);
        }
    }

    private function normalizeSlug($slug)
    {

        // return str_replace('-' , '_' , $slug);
        $newSlug = '';
        $arrWords = explode('-', $slug);

        if (is_array($arrWords)) {
            foreach ($arrWords as $word) {
                $newSlug .= ucfirst(strtolower($word));
            }
            return $newSlug;
        } else {
            return ucfirst(strtolower($slug));
        }
    }
}
