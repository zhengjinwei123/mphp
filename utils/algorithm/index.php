<?php
/**
 * Created by PhpStorm.
 * User: zhengjinwei
 * Date: 2017/3/29
 * Time: 11:57
 */
namespace Utils\Algorithm;

class FormulaUtil
{
    // 权重随机算法
    public static function randomIndex($rateArray)
    {
        $sum = 0;
        $left = 0;
        $right = 0;
        foreach ($rateArray as $value) {
            $sum += $value * 100;
        }

        $temp = mt_rand(0, $sum);
        foreach ($rateArray as $key => $value) {
            $right += $value * 100;

            // 处理边界情况
            if ($right == $sum && $temp == $right) {
                return $key;
            }

            if ($left <= $temp && $temp < $right) {
                return $key;
            }
            $left += $value * 100;
        }

        return -1;
    }
}

;

