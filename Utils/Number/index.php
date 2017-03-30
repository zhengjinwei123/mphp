<?php
/**
 * Created by PhpStorm.
 * User: zhengjinwei
 * Date: 2017/3/29
 * Time: 12:03
 */
namespace Utils\Number;

class NumberUtil
{
    //将字符串类型的数字转换为整数
    public static function toInt($s)
    {
        $i = (bccomp($s, '2147483647') > 0) ? $s : intval($s);
        return $i;
    }

    //类型转换
    public static function toNumber($value)
    {
        if (is_numeric($value)) {
            if (strpos($value, '.')) {
                return floatval($value);
            } else {
                return self::toInt($value);
            }
        }
        return $value;
    }
}