<?php
/**
 * Created by PhpStorm.
 * User: zhengjinwei
 * Date: 2017/3/29
 * Time: 12:07
 */
namespace Utils\File;

class FileUtil
{
    private static $_instance = null;

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 递归的获取某个目录指定的文件
     * @param $dir
     * @return array
     */
    public static function getFiles($dir)
    {
        $files = [];
        if (!is_dir($dir) || !file_exists($dir)) {
            return $files;
        }

        $directory = new \RecursiveDirectoryIterator($dir);
        $iterator = new \RecursiveIteratorIterator($directory);
        foreach ($iterator as $info) {
            $file = $info->getFilename();
            if ($file == '.' || $file == '..') {
                continue;
            }
            $files[] = $info->getPathname();
        }

        return $files;
    }

    public static function isDirectory($dir)
    {
        return is_dir($dir);
    }

    public static function isFile($filename)
    {
        return is_file($filename);
    }

    public static function fileExists($filePath)
    {
        return file_exists($filePath);
    }

    public static function read($filePath)
    {
        if (!self::fileExists($filePath)) {
            return null;
        }
        return file_get_contents($filePath);
    }

    public static function write($filePath, $content, $flag = FILE_APPEND)
    {
        if (!self::fileExists($filePath)) {
            return false;
        }
        if ($flag == FILE_APPEND) {
            file_put_contents($filePath, $content, FILE_APPEND);
        } else {
            file_put_contents($filePath, $content);
        }
        return true;
    }

    public static function remove($filePath)
    {
        if (!self::fileExists($filePath)) {
            return false;
        }
        delete($filePath);
    }
}

