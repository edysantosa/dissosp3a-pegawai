<?php namespace sys;

class Database
{
    private $schema;

    private static $instance = null;

    public function __construct()
    {
        $config = Config::claim();

        if (!isset($config['settings']['databaseConnection']['timezone'])) {
            $offset = (new \DateTime)->getOffset();
            $offsetHours = round(abs($offset) / 3600);
            $offsetMinutes = round((abs($offset) - $offsetHours * 3600) / 60);
            $offsetString  = ($offset < 0 ? '-' : '+');
            $offsetString .= (strlen($offsetHours) < 2 ? '0' : '').$offsetHours;
            $offsetString .= ':';
            $offsetString .= (strlen($offsetMinutes) < 2 ? '0' : '').$offsetMinutes;
            $config['settings']['databaseConnection']['timezone'] = $offsetString;
        }

        $capsule = new \Illuminate\Database\Capsule\Manager;
        $capsule->addConnection($config['settings']['databaseConnection']);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        $this->schema = $capsule;
        self::$instance = $this;
    }

    public function __invoke($container)
    {
        return $this->schema;
    }

    public static function instance()
    {
        if (!is_null(self::$instance)) {
            return self::$instance;
        }

        return new self();
    }
}
