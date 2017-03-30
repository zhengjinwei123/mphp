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


    // PHP数组转换成字串
    public static function phpArrayeval($array, $level = 0)
    {
        $space = '';
        for($i = 0; $i <= $level; $i++) {
            $space .= "\t";
        }

        $evaluate = "Array\n{$space}(\n";

        $comma = $space;
        foreach($array as $key => $val) {
            $key = is_string($key) ? '\''.addcslashes($key, '\'\\').'\'' : $key;
            $val = !is_array($val) && (!preg_match("/^\-?\d+$/", $val) || strlen($val) > 12 || substr($val, 0, 1)=='0') ? '\''.addcslashes($val, '\'\\').'\'' : $val;

            if(is_array($val)) {
                $evaluate .= "{$comma}{$key} => " . self::phpArrayeval($val, $level + 1);
            } else {
                $evaluate .= "{$comma}{$key} => {$val}";
            }

            $comma = ",\n$space";
        }

        $evaluate .= "\n$space)";

        return $evaluate;
    }

    /**
     * 去除代码中的空白和注释
     * @param string $content 代码内容
     * @return string
     */
    public static function stripWhitespace($content)
    {
        $stripStr   = '';

        //分析php源码
        $tokens     = token_get_all($content);
        $last_space = false;

        for ($i = 0, $j = count($tokens); $i < $j; $i++) {
            if (is_string($tokens[$i])) {
                $last_space = false;
                $stripStr  .= $tokens[$i];
            } else {
                switch ($tokens[$i][0]) {
                    //过滤各种PHP注释
                    case T_COMMENT:
                    case T_DOC_COMMENT:
                        break;
                    //过滤空格
                    case T_WHITESPACE:
                        if (!$last_space) {
                            $stripStr  .= ' ';
                            $last_space = true;
                        }
                        break;
                    case T_START_HEREDOC:
                        $stripStr .= "<<<ZBDOC\n";
                        break;
                    case T_END_HEREDOC:
                        $stripStr .= "ZBDOC;\n";
                        for($k = $i+1; $k < $j; $k++) {
                            if(is_string($tokens[$k]) && $tokens[$k] == ';') {
                                $i = $k;
                                break;
                            } else if($tokens[$k][0] == T_CLOSE_TAG) {
                                break;
                            }
                        }
                        break;
                    default:
                        $last_space = false;
                        $stripStr  .= $tokens[$i][1];
                }
            }
        }

        return $stripStr;
    }

    public static function cache($temp = array())
    {
        $str = self::phpArrayeval($temp);
        $str = "<?PHP Return ".$str.";";
        $str = self::stripWhitespace($str);
        File\FileUtil::write("./a.php", $str, null);
    }
}