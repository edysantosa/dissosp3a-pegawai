<?php namespace sys;

class Config
{
    final private function __construct()
    {
    }

    public static function claim()
    {
        if (!defined('APPATH')) {
            define('APPATH', __DIR__ . '/../application');
        }
        $configfile = APPATH . '/' . 'config.php';

        if (!file_exists($configfile)) {
            throw new \Exception('Config not found');
        }

        return require($configfile);
    }
}
