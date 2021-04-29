<?php namespace sys;

class Sitebase
{
    private static $instance = null;
    
    private function __construct()
    {
    }

    public function __set($var, $val)
    {
        static::add($var, $val);
    }

    public function __get($var)
    {
        return static::get($var, null);
    }

    public static $bases = [];

    public static function instance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        
        return static::$instance;
    }

    public static function add($name, $value)
    {
        static::$bases[ $name ] = $value;
    }

    public static function get($name = null, $defaultValue = null)
    {
        if (is_null($name)) {
            return static::$bases;
        }

        return isset(static::$bases[ $name ]) ? static::$bases[ $name ] : $defaultValue;
    }

    public static function getInTwig()
    {
        $temp = [];

        foreach (static::$bases as $key => $val) {
            $temp[] = [
                'name' => $key,
                'value' => $val
            ];
        }

        return $temp;
    }

    public static function remove($name)
    {
        unset(static::$bases[ $name ]);
    }
}
