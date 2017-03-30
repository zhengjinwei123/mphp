<?php
/**
 * Created by PhpStorm.
 * User: zhengjinwei
 * Date: 2017/3/29
 * Time: 12:07
 */
namespace MPHP\Utils\File;

class FileUtil
{
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

    public static function createFile($filePath)
    {
        $handle = fopen($filePath, "w+");
        if ($handle) {
            fclose($handle);
            return true;
        }
        return false;
    }

    public static function get_extension($file)
    {
        $info = pathinfo($file);
        return $info['extension'];
    }

    public static function write($filePath, $content, $flag = FILE_APPEND)
    {
        if (!self::fileExists($filePath)) {
            self::createFile($filePath);
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
        unlink($filePath);
    }

    // PHP数组转换成字串
    public static function phpArrayEval($array, $level = 0)
    {
        if (!is_array($array)) {
            return $array;
        }
        $space = '';
        for ($i = 0; $i <= $level; $i++) {
            $space .= "\t";
        }

        $evaluate = "Array\n{$space}(\n";

        $comma = $space;
        foreach ($array as $key => $val) {
            $key = is_string($key) ? '\'' . addcslashes($key, '\'\\') . '\'' : $key;
            $val = !is_array($val) && (!preg_match("/^\-?\d+$/", $val) || strlen($val) > 12 || substr($val, 0, 1) == '0') ? '\'' . addcslashes($val, '\'\\') . '\'' : $val;

            if (is_array($val)) {
                $evaluate .= "{$comma}{$key} => " . self::phpArrayEval($val, $level + 1);
            } else {
                $evaluate .= "{$comma}{$key} => {$val}";
            }

            $comma = ",\n$space";
        }

        $evaluate .= "\n$space)";

        return $evaluate;
    }

    //去除代码中的空白和注释
    public static function stripWhitespace($content)
    {
        $stripStr = '';

        //分析php源码
        $tokens = token_get_all($content);
        $last_space = false;

        for ($i = 0, $j = count($tokens); $i < $j; $i++) {
            if (is_string($tokens[$i])) {
                $last_space = false;
                $stripStr .= $tokens[$i];
            } else {
                switch ($tokens[$i][0]) {
                    //过滤各种PHP注释
                    case T_COMMENT:
                    case T_DOC_COMMENT:
                        break;
                    //过滤空格
                    case T_WHITESPACE:
                        if (!$last_space) {
                            $stripStr .= ' ';
                            $last_space = true;
                        }
                        break;
                    case T_START_HEREDOC:
                        $stripStr .= "<<<ZBDOC\n";
                        break;
                    case T_END_HEREDOC:
                        $stripStr .= "ZBDOC;\n";
                        for ($k = $i + 1; $k < $j; $k++) {
                            if (is_string($tokens[$k]) && $tokens[$k] == ';') {
                                $i = $k;
                                break;
                            } else if ($tokens[$k][0] == T_CLOSE_TAG) {
                                break;
                            }
                        }
                        break;
                    default:
                        $last_space = false;
                        $stripStr .= $tokens[$i][1];
                }
            }
        }

        return $stripStr;
    }
}

