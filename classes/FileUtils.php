<?php

class FileUtils
{
    public static function checkFileExists(array $file): bool
    {
        print((!isset($file) || empty($file['tmp_name'])));
        return (!isset($file) || empty($file['tmp_name']));
    }

    public static function checkFileExtension(array $file, string $extension): bool
    {
        return (pathinfo($file['name'], PATHINFO_EXTENSION) !== $extension);
    }

    public static function checkUploadOk(array $file): bool
    {
        return (!isset($file['error']) || $file['error'] !== 0);
    }
}