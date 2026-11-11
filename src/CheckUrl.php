<?php

namespace JLTRY\LibFavorites;

\defined('_JEXEC') or die;

function checkurl($url)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_TIMEVALUE, time());
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_NOBODY, true);
    curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:19.0) Gecko/20100101 Firefox/19.0');
    $response = curl_exec($curl);
    if ($response === false) { return false; }
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return ($status >= 200 && $status < 308) ? true : $status;
}

function checkurl2($url)
{
    $headers = @get_headers($url);
    return strpos($headers[0], '404') === false ? true : $headers[0];
}

function get_headers2($url, $format = 0)
{
    $url_info = parse_url($url);
    $port = isset($url_info['port']) ? $url_info['port'] : 80;
    $fp = fsockopen($url_info['host'], $port, $errno, $errstr, 30);
    if (!$fp) { return false; }
    $head = 'HEAD ' . @$url_info['path'] . '?' . @$url_info['query'];
    $head .= " HTTP/1.0\r\nHost: " . @$url_info['host'] . "\r\n\r\n";
    fputs($fp, $head);
    while (!feof($fp)) {
        if ($header = trim(fgets($fp, 1024))) {
            if ($format == 1) {
                $parts = explode(':', $header);
                if ($parts[0] == $header) { $headers['status'] = $header; }
                else { $headers[strtolower($parts[0])] = trim($parts[1]); }
            } else { $headers[] = $header; }
        }
    }
    fclose($fp);
    return true;
}
