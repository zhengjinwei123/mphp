<?php


function writeFile($fileName, $data)
{
    $fp = fopen($fileName, "w");
    fwrite($fp, iconv('UTF-8', 'GB2312', $data)); //写入数据
    fclose($fp); //关闭文件句柄
    return true;
}

function readFileContent($fileName)
{
    if (!file_exists($fileName)) {
        throw Exception("file $fileName not exists");
        return;
    }
    return file_get_contents($fileName);
}

function getCsvFileContent($file)
{
    $file = fopen($file, 'r');
    $infoList = [];
    while ($data = fgetcsv($file)) { //每次读取CSV里面的一行内容
        $infoList[] = $data;
    }
    fclose($file);
    return $infoList;
}

function getFileBaseName($filePath, $ext = ".csv")
{
    $filePath = str_replace("\\", "/", $filePath);
    $arr = explode("/", $filePath);
    $len = count($arr);
    $name = $arr[$len - 1];
    return str_replace($ext, "", $name);
}

function arrayIndexByKey($key, $arr = array())
{
    $n = 0;
    foreach ($arr as $k => $v) {
        if ($k == $key) {
            return $n;
        }
        $n += 1;
    }
    return false;
}

function arr2PhpString($arr)
{
    $json = json_encode($arr);

    $str = "<?php\r\n\treturn array";
    $temp = preg_replace('/{/', " ( ", $json);
    $temp = preg_replace('/}/', " )", $temp);
    $temp = preg_replace('/:/', " => ", $temp);
    $temp = preg_replace('/\[/', " array(", $temp);
    $temp = preg_replace('/\]/', " )", $temp);
    $temp = preg_replace('/\"(\d)\"/', '$1', $temp);

    $str .= $temp;
    $str .= ";";
    return $str;
}

/**
 * 读取文件夹下所有文件
 * @param $dir
 * @return array|bool
 */
function getFiles($dir)
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

// 压缩文件夹下所有php文件
function stripCommentAndWhitespace($path = '')
{
    if (empty($path)) {
        echo '请指定要操作的文件路径';
        return false;
    } else {
        $path = str_replace('\\', '/', $path);
    }
    if ($handle = opendir($path)) {
        while (false !== ($fileName = readdir($handle))) {
            if ($fileName != "." && $fileName != "..") {
                if (is_file($path . '/' . $fileName)) {
                    // 压缩.php后缀文件 　　　　　　　　　　
                    $suffix = pathinfo($path . '/' . $fileName, PATHINFO_EXTENSION);
                    if ($suffix == 'php') {
                        $newFile = php_strip_whitespace($path . '/' . $fileName);
                        file_put_contents($path . '/' . $fileName, $newFile);
                    }
                }
                if (is_dir($path . '/' . $fileName)) {
                    stripCommentAndWhitespace($path . '/' . $fileName);
                }
            }
        }
        closedir($handle);
    }
}

// PHP数组转换成字串
function phpArrayeval($array, $level = 0)
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
            $evaluate .= "{$comma}{$key} => " . phpArrayeval($val, $level + 1);
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
function stripWhitespace($content)
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

// PHP cache文件生成
function phpCacheWrite($filename, $values, $var='')
{
    $content = !empty($var) ? "${$var}=" . phpArrayeval($values) : "Return " . phpArrayeval($values);
    $cachetext = "<?php\r\n{$content};";
    $cachetext = stripWhitespace($cachetext);


    return writeFile($filename, $cachetext);
}