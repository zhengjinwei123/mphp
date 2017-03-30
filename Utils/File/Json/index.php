<?php
/**
 * Created by PhpStorm.
 * User: zhengjinwei
 * Date: 2017/3/30
 * Time: 16:19
 * 将json 转 php
 */

namespace MPHP\Utils\File\Json;

use MPHP\Utils\File;

class JsonUtil
{
    public static function writeCache($jsonFilePath)
    {
        if (!File\FileUtil::fileExists($jsonFilePath)) {
            return false;
        }
        $content = File\FileUtil::read($jsonFilePath);

        $content = json_decode($content, JSON_UNESCAPED_UNICODE);


        return self::cache($jsonFilePath, $content);
    }

    public static function cache($csvFilePath, $temp = array())
    {
        $str = File\FileUtil::phpArrayEval($temp);
        $str = "<?PHP Return " . $str . ";";
        $str = File\FileUtil::stripWhitespace($str);
        $phpFile = str_replace(".json", ".php", $csvFilePath);

        File\FileUtil::write($phpFile, $str, null);
        return true;
    }
}