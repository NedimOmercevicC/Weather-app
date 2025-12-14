<?php
// Set the reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL ^ (E_NOTICE | E_DEPRECATED));

class Config {
    // Database Configuration
    public static function DB_HOST() {
        return 'localhost';
    }

    public static function DB_NAME() {
        return 'weather_app_db';
    }

    public static function DB_USER() {
        return 'root';
    }

    public static function DB_PASSWORD() {
        return ''; // Update with your MySQL password if needed
    }

    public static function JWT_SECRET() {
        return 'v4+39439f9@#_SECRET_KEY_FOR_LAB_6_#@d33d3d';
    }
}
?>

