<?php namespace sys;

class Controller
{
    public static $instance = [];

    public static $container;

    protected $params = [];

    public function __get($name)
    {
        if (isset(static::$container[$name])) {
            return static::$container[$name];
        }

        return isset($this->params[$name]) ? $this->params[$name] : null;
    }

    public function __set($name, $value)
    {
        if (isset(static::$container[$name])) {
            throw new Exception('{'. $name .'} is limited, can\'t set the value');
        }

        $this->params[$name] = $value;
    }

    public function getContainer()
    {
        return static::$container;
    }

    public static function instance($container = null)
    {
        $classname = get_called_class();

        if (isset(Controller::$instance[$classname])) {
            return Controller::$instance[$classname];
        }

        if (is_null($container)) {
            static::$container = Controller::$container;
        } else {
            static::$container = $container;
        }
        
        return Controller::$instance[$classname] = new static;
    }
}