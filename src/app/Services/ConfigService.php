<?php

namespace App\Services;

use CodeIgniter\Config\BaseConfig;

class ConfigService
{
    public static function isDevelopment() : bool
    {
        return env('CI_ENVIRONMENT') === 'development';
    }

    public static function getRedisPrefix(): string
    {
        return self::isDevelopment() ? "dev_" : "prod_";
    }
}