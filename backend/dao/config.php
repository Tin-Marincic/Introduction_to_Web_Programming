<?php


// Set the reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL ^ (E_NOTICE | E_DEPRECATED));


class Config
{
   public static function DB_NAME()
   {
       return 'web_db'; 
   }
   public static function DB_PORT()
   {
       return  3306;
   }
   public static function DB_USER()
   {
       return 'root';
   }
   public static function DB_PASSWORD()
   {
       return 'Tm06103006!';
   }
   public static function DB_HOST()
   {
       return '127.0.0.1';
   }

   public static function JWT_SECRET() {
       return 'e9f8a4b2c6d71e13f3RANDOMa9ba08c4d5e7b9f8c2a1e6';
   }
}
