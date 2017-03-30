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

    //-----------------------------------------------------------------------------
    //延后5小时 早上5点相当于凌晨0点
    protected static function _timeCorrect($tm = -1)
    {
        $tm = ($tm === -1) ? time() : $tm;
        $tm -= 5 * 60 * 60;
        $tm = ($tm < 0) ? 0 : $tm;

        return $tm;
    }

    // 游戏系统刷新时间 年
    public static function getGameYear($tm = -1)
    {
        $tm = self::_timeCorrect($tm);

        return intval(date('Y', $tm));
    }

    // 游戏系统刷新时间 月
    public static function getGameMonth($tm = -1)
    {
        $tm = self::_timeCorrect($tm);

        return intval(date('m', $tm));
    }

    // 游戏系统刷新时间 日
    public static function getGameDay($tm = -1)
    {
        $tm = self::_timeCorrect($tm);

        return intval(date('j', $tm));
    }

    // 游戏系统刷新时间 年月日
    public static function getGameYmd($tm = -1)
    {
        $tm = self::_timeCorrect($tm);

        return intval(date('Ymd', $tm));
    }

    // 游戏系统刷新时间 年月
    public static function getGameYm($tm = -1)
    {
        $tm = self::_timeCorrect($tm);

        return intval(date('Ym', $tm));
    }

    // 游戏系统刷新时间 周几
    public static function getGameWeek($tm = -1)
    {
        $tm = self::_timeCorrect($tm);
        $w = intval(date('w', $tm));
        $w = ($w == 0) ? 7 : $w;

        return $w;
    }

    // 游戏系统刷新时间 本周是本年第几周
    public static function getGameWeekInYear($tm = -1)
    {
        $tm = self::_timeCorrect($tm);
        $w = intval(date('W', $tm));

        return $w;
    }

    // 游戏系统刷新时间 获取本月有多少天
    public static function getGameT($tm = -1)
    {
        $tm = self::_timeCorrect($tm);
        $w = intval(date('t', $tm));

        return $w;
    }

    // 游戏系统刷新时间 本月还剩余天数
    public static function getGameLeftDaysInMonth()
    {
        // $d = cal_days_in_month(CAL_GREGORIAN, self::getGameMonth(), self::getGameYear());
        $d = self::getGameT();
        $leftDays = $d - self::getGameDay();
        $leftDays = ($leftDays > 0) ? $leftDays : 0;
        return $leftDays;
    }

    // 游戏系统刷新时间 指定时间是否是今天
    public static function isGameToday($tm)
    {
        return (self::getGameYmd($tm) == self::getGameYmd());
    }

    // 游戏系统刷新时间 指定时间是否是本月
    public static function isGameMonth($tm, $tm2 = -1)
    {
        return (self::getGameYm($tm) == self::getGameYm($tm2));
    }

    // 游戏系统刷新时间 间隔天数
    public static function getGameDiffDays($tm = -1)
    {
        $curTm = self::_timeCorrect();
        $tm = self::_timeCorrect($tm);

        if (date('Ymd', $tm) == date('Ymd', $curTm)) {
            return 0;
        }

        $diff = floor(($curTm - $tm) / 86400);
        $diff = ($diff <= 0) ? 1 : $diff;

        return $diff;
    }

    //-----------------------------------------------------------------------------
    // 当前时间是否在指定时间段内
    public static function hasBetweenTime($startTime, $endTime)
    {
        $time = time();
        if ($time >= strtotime($startTime) && $time <= strtotime($endTime))
            return true;

        return false;
    }

    // 当前时间是否指定小时范围内
    public static function hasBetweenHour($startHour, $endHour)
    {
        $d = date('H');
        if ($d >= $startHour && $d < $endHour)
            return true;

        return false;
    }

    // 计算星期几在本周的起始时间戳
    public static function calcTmByWeek($w)
    {
        $time = time();
        $dw = date('w', $time);
        $dw = ($dw == 0) ? 7 : $dw;
        $w = ($w == 0) ? 7 : $w;

        $tm = strtotime(date('Y-m-d', $time));
        if ($dw == $w) {
            return $tm;
        } else if ($w > $dw) {
            return $tm + ($w - $dw) * 86400;
        } else {
            return $tm - ($dw - $w) * 86400;
        }
    }

    //-----------------------------------------------------------------------------
    //CD
    public static function leftCD($cd, $tm)
    {
        $tmDiff = time() - $tm;
        $leftTm = $cd - $tmDiff;
        $leftTm = ($leftTm > 0) ? $leftTm : 0;

        return $leftTm;
    }

}










