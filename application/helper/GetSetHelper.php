<?php namespace app\helper;

class GetSetHelper implements \ArrayAccess
{
    private $tube = [];

    public function __construct($tube = [])
    {
        $this->tube = $tube;
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function get(...$params)
    {
        if (count($params) <= 0) {
            return $this->toArray();
        }
        
        $name = array_shift($params);
        
        if (count($params) > 0) {
            $defaultValue = array_shift($params);
        }
        
        if (!isset($this->tube[$name]) || is_null($this->tube[$name]) || empty($this->tube[$name])) {
            $vars = get_defined_vars();
            if (array_key_exists('defaultValue', $vars)) {
                return $defaultValue;
            } else {
                return null;
                //throw new \Exception('Index "'. $name .'" undefined');
            }
        }

        return $this->tube[$name];
    }

    public function set($name, $value, $force = false)
    {
        if (!isset($this->tube[$name]) && !$force) {
            throw new \Exception('Index "'. $name .'" undefined');
        }

        $this->tube[$name] = $value;

        return $this;
    }

    public function toArray()
    {
        return $this->tube;
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetExists($offset)
    {
        return isset($this->tube[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->tube[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }
}
