<?php
/**
 * Created by PhpStorm.
 * User: zhengjinwei
 * Date: 2017/3/29
 * Time: 12:10
 */
namespace Utils\Socket;

class SocketUtil{
    // http请求函数
    public static function socketRequest($url, $post = false, $params = '', $limit = 0, $cookie = '', $timeout = 10, $response = FALSE, $block = false)
    {
        if($post == false) {
            if (is_array($params)) {
                $url .= (strpos($url, '?') === false ? '?' : '&') . self::paramsQuery($params);
            } else if($params) {
                $url .= (strpos($url, '?') === false ? '?' : '&') . $params;
            }
        }

        $uri = parse_url($url);
        $host = $uri['host'];
        $port = !empty($uri['port']) ? $uri['port'] : 80;
        $path = $uri['path'] ? $uri['path'].($uri['query'] ? '?'.$uri['query'] : '') : '/';

        if($post)
        {
            $content = self::paramsQuery($params);

            $out  = "POST $path HTTP/1.0\r\n";
            $out .= "Accept: */*\r\n";
            $out .= "Accept-Language: zh-cn\r\n";
            $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $out .= "Host: $host\r\n";
            $out .= 'Content-Length: '.strlen($content)."\r\n";
            $out .= "Connection: Close\r\n";
            $out .= "Cache-Control: no-cache\r\n";
            $out .= "Cookie: $cookie\r\n";
            $out .= "\r\n";
            $out .= $content;
        } else {
            $out  = "GET $path HTTP/1.0\r\n";
            $out .= "Accept: */*\r\n";
            $out .= "Accept-Language: zh-cn\r\n";
            $out .= "Host: $host\r\n";
            $out .= "Connection: Close\r\n";
            $out .= "Cookie: $cookie\r\n";
            $out .= "\r\n";
        }

        $fp = fsockopen($host, $port, $errno, $error, $timeout);
        if(!$fp) {
            return "{$errno}:{$error}";
        }

        $return = '';
        stream_set_blocking($fp, $block);
        stream_set_timeout($fp, $timeout);
        fwrite($fp, $out);
        if($response) {
            $status = stream_get_meta_data($fp);
            if(!$status['timed_out']) {
                while (!feof($fp)) {
                    if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n")) {
                        break;
                    }
                }
                $stop = false;
                while(!feof($fp) && !$stop) {
                    $data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
                    $return .= $data;
                    if($limit) {
                        $limit -= strlen($data);
                        $stop = $limit <= 0;
                    }
                }
            }
        }

        fclose($fp);
        return $return;
    }

    // curl请求
    public static function curlRequest($url, $post = false, $params = '', $https = false, $ca = '', $timeout = 10, $needHeader = false, $cookie='')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_USERAGENT, '');

        if($https && $ca) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);     // 只信任CA颁布的证书
            curl_setopt($ch, CURLOPT_CAINFO, $ca);              // CA根证书（用来验证的网站证书是否是CA颁布）
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);        // 检查证书中是否设置域名，并且是否与提供的主机名匹配
        } else if($https && !$ca) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    // 信任任何证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);        // 检查证书中是否设置域名
        }

        if ($needHeader) {
            curl_setopt($ch, CURLOPT_HEADER, true);
        }

        if ($post) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
            curl_setopt($ch, CURLOPT_POST, true);

            if (is_array($params)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, self::paramsQuery($params));
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            }
        } else {
            if (is_array($params)) {
                $url .= (strpos($url, '?') === false ? '?' : '&') . self::paramsQuery($params);
            }
            else if($params) {
                $url .= (strpos($url, '?') === false ? '?' : '&') . $params;
            }
        }

        if(!empty($cookie)){
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }

        curl_setopt($ch, CURLOPT_URL, $url);

        $result = curl_exec($ch);

        if ($needHeader) {
            $tmp = $result;
            $result = array();
            $info = curl_getinfo($ch);
            $result['header'] = substr($tmp, 0, $info['header_size']);
            $result['body'] = trim(substr($tmp, $info['header_size']));
        }

        $errno = curl_errno($ch);
        if($errno) {
            $error = curl_error($ch);
            $result = "{$errno}:{$error}";
        }

        curl_close($ch);
        return $result;
    }
}