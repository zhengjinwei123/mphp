<?php
/**
 * Created by PhpStorm.
 * User: zhengjinwei
 * Date: 2017/3/30
 * Time: 14:42
 * 将csv文件转为 php
 */

namespace MPHP\Utils\File\Csv;

use MPHP\Utils\File;

class CsvUtil
{
    public static function parse($csvFilePath, $key = null, $filter = null)
    {
        $content = File\FileUtil::read($csvFilePath);
        if (empty($content)) {
            return false;
        }

        $temLines = explode("\r\n", $content);
        if (empty($temLines)) {
            return false;
        }

        $tmp = $keys = array();
        $keyIndex = -1;
        $len = count($temLines);
        foreach ($temLines as $k => $v) {
            $tempColumn = explode(",", $v);
            if (empty($tempColumn)) {
                continue;
            }

            if ($k <= 1) {
                if ($k == 1) {
                    $keys = $tempColumn;
                }
                continue;
            } else if ($k == 2) {
                $keyIndex = array_search($key, $keys);
            }
            if ($k == $len - 1) {
                break;
            }


            $t = array();
            foreach ($tempColumn as $k1 => $v1) {
                if (empty($filter)) {
                    $t[$keys[$k1]] = $v1;
                } else {
                    if (in_array($keys[$k1], $filter)) {
                        $t[$keys[$k1]] = $v1;
                    }
                }
            }
            if ($keyIndex == -1) {
                $tmp[] = $t;
            } else {
                $tmp[$tempColumn[$keyIndex]] = $t;
            }
        }

        self::cache($tmp);
    }

    public static function cache($temp = array())
    {
        $str = File\FileUtil::phpArrayEval($temp);
        $str = "<?PHP Return " . $str . ";";
        $str = File\FileUtil::stripWhitespace($str);
        File\FileUtil::write("./a.php", $str, null);
    }
}