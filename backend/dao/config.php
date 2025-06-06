<?php

// Enable error reporting (optional for prod)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL ^ (E_NOTICE | E_DEPRECATED));

class Config
{
    public static function DB_NAME()
    {
        return self::get_env("DB_NAME", "web_db");
    }

    public static function DB_PORT()
    {
        return self::get_env("DB_PORT", 3306);
    }

    public static function DB_USER()
    {
        return self::get_env("DB_USER", "root");
    }

    public static function DB_PASSWORD()
    {
        return self::get_env("DB_PASSWORD", "Tm06103006!");
    }

    public static function DB_HOST()
    {
        return self::get_env("DB_HOST", "127.0.0.1");
    }

    public static function JWT_SECRET()
    {
        return self::get_env("JWT_SECRET", "e9f8a4b2c6d71e13f3RANDOMa9ba08c4d5e7b9f8c2a1e6");
    }

   private static function get_env($name, $default)
    {
        if (isset($_ENV[$name]) && trim($_ENV[$name]) !== "") {
            return $_ENV[$name];
        } elseif (isset($_SERVER[$name]) && trim($_SERVER[$name]) !== "") {
            return $_SERVER[$name];
        } else {
            return $default;
        }
    }


}
