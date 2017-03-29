<?php
/**
 * Created by PhpStorm.
 * User: zhengjinwei
 * Date: 2017/3/29
 * Time: 11:43
 */

namespace Utils\Date;

class DateUtil
{
    public static function timeDiff($t1, $t2)
    {
        return abs($t1 - $t2);
    }

    public static function dateDiff($d1, $d2)
    {
        $a = strtotime($d1);
        $b = strtotime($d2);
        return abs($a - $b);
    }
}










