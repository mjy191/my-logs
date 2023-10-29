<?php

namespace Mjy191\MyLogs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Mjy191\Tools\Tools;

class MyLogs
{

    /**
     * 记录日志
     * @param $logName
     * @param $content
     * @return false|int
     */
    public static function write($logName, $content)
    {
        $logId = self::logId();
        if (!is_string($content)) {
            $content = self::toString($content);
        }
        $log_path = base_path() . '/logs/' . date('Ym');
        $uri = 'uri[' . Tools::getUri() . ']';
        if (PHP_SAPI == 'cli') {
            $uri .= ' cli[' . implode(' ', $_SERVER['argv']) . ']';
        }
        $log_format = date('Y-m-d H:i:s')
            . " {$uri}"
            . " cgi[" . PHP_SAPI . "]"
            . " logId[{$logId}] "
            . " {$logName}[{$content}]" . PHP_EOL;
        $date = date('YmdH');
        if (!is_dir($log_path)) {
            mkdir($log_path, 0777, true);
        }
        return file_put_contents($log_path . '/' . $date . '.log', $log_format, FILE_APPEND);
    }

    /**
     * @param $param
     * @return false|string
     */
    public static function toString($param)
    {
        if (is_string($param)) {
            return $param;
        }
        return json_encode($param, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 生成日志logId
     * @return string
     */
    public static function logId()
    {
        if (!defined('logId')) {
            $logId = date('YmdHis') . uniqid();
            define('logId', $logId);
        }
        return logId;
    }

    /**
     */

    /**
     * 记录db操作日志
     */
    public static function dBLog()
    {
        DB::listen(function ($query) {
            $tmp = str_replace('?', '"' . '%s' . '"', $query->sql);
            $sql = vsprintf($tmp, $query->bindings);
            $sql = str_replace("\\", "", $sql);
            self::write('mysql', $sql);
        });
    }

    /**
     * 使用中间件记录请求Logs.php
     * @param $request
     */
    public static function reqLog($request)
    {
        $body = Request::instance()->getContent();
        if($body){
            self::write('request',$body);
        }else{
            self::write('request',$request->post());
        }
    }

    /**
     * 使用中间件记录返回数据Logs.php
     * @param $request
     * @param $response
     */
    public static function resLog($request, $response)
    {
        if($request->method()=='POST'){
            self::write('response', $response->getContent());
        }
    }
}
