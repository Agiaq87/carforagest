<?php

class Ssh2Checker
{
    private static ?Ssh2Checker $instance = null;

    private function __construct()
    {

    }

    public static function getInstance(): Ssh2Checker
    {
        if(self::$instance === null) {
            $c = __CLASS__;
            self::$instance = new $c();
        }

        return self::$instance;
    }

    public static function isSsh2LibraryInstalled(): bool
    {
        return extension_loaded('ssh2');
    }
}