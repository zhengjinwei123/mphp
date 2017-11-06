<?php

/**
 * create by zhengjw @ 2017.10.16
 */
include "./utils.php";

// 将csv 转为 PHP
$inputCsvFilePath = dirname(__FILE__) . "/configs/";
$outputPath = dirname(__FILE__) . "/gameConfigs/";
$minify = true;

// ------------------------------新增文件需要在这里配置-----------------------------------
$phpFileList = array(
    "shop" => array(
        "index" => null
    ),
    "EnemyParamTable" => array(
        "index" => "ENMId",
    ),
    "RegionInfoTable" => array(
        "index" => null
    )
);


//----------------------------------end--------------------------------------------------
$fileList = getFiles($inputCsvFilePath);
if (empty($fileList)) {
    echo "没有有效的配置文件\r\n";
}

function printError($err)
{
    echo "$err\r\n";
    exit();
}


foreach ($fileList as $file) {
    $baseName = getFileBaseName($file);
    $arr = getCsvFileContent($file);
    if (count($arr) <= 1) {
        printError("invalid format file:{$file}");
        break;
    }

    if (!isset($phpFileList[$baseName])) {
        continue;
    }
    $keys = $arr[0];
    $cfg = $phpFileList[$baseName];
    $keyIndex = false;
    if (isset($cfg['index']) && $cfg['index']) {
        $indexKey = $cfg['index'];
        if (!in_array($indexKey, $keys)) {
            printError("invalid format file:{$file}");
            break;
        }
        $keyIndex = arrayIndexByKey($indexKey, $keys);
    }

    $retData = array();
    foreach ($arr as $k => $v) {
        if ($k == 0) {
            continue;
        }


        $_v = array();
        foreach ($v as $key => $value) {
            $keyName = arrayKeyByIndex($key,$keys);

            if (is_numeric($value)) {
                $_v[$keyName] = floatval($value);
            } else {
                $lowerStr = strtolower($value);
                if($lowerStr == 'false'){
                    $_v[$keyName] = 0;
                }else if($lowerStr == 'true'){
                    $_v[$keyName] = 1;
                }else{
                    $_v[$keyName] = $value;
                }
            }
        }

        if ($keyIndex !== false) {
            $retData[$v[$keyIndex]] = $_v;
        } else {
            $retData[] = $_v;
        }
    }

    $outFileName = $outputPath . "$baseName" . ".php";
    $ret = phpCacheWrite($outFileName,$retData);
    if($ret){
        echo "gen $outFileName configs success\r\n";
    }
//    writeFile($outFileName, arr2PhpString($retData));
}

//if ($minify) {
//    stripCommentAndWhitespace($outputPath);
//}





