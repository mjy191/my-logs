<?php
namespace Mjy191\MyLogs;

use App\Common\Tools;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\DocBlock\Tags\Link;

class MyLogs {

    /**
     * 记录日志
     * @param $logName
     * @param $content
     * @return false|int
     */
    public static function write($logName,$content){
        $logId = self::logId();
        if(!is_string($content)){
            $content = self::toString($content);
        }
        $backtrace = debug_backtrace();
        if(PHP_SAPI=='cli'){
            $uri = 'cli['.implode(' ',$_SERVER['argv']).']';
            $log_path=base_path().'/logs/cli/'.date('Ym');
        }else{
            $uri = 'uri['.$_SERVER['REQUEST_URI'].']';
            $log_path=base_path().'/logs/'.date('Ym');
        }
        $log_format = date('Y-m-d H:i:s')
            ." {$uri}"
            ." [{$backtrace[0]['file']}:{$backtrace[0]['line']}]"
            ." cgi[".PHP_SAPI."]"
            ." logId[{$logId}] "
            ." {$logName}[{$content}]".PHP_EOL;
        $date = date('YmdH');
        if(!is_dir($log_path)){
            mkdir($log_path,0777,true);
        }
        return file_put_contents($log_path.'/'.$date.'.log', $log_format, FILE_APPEND);
    }

    /**
     * @param $param
     * @return false|string
     */
    public static function toString($param){
        if(is_string($param)){
            return $param;
        }
        return json_encode($param,JSON_UNESCAPED_UNICODE);
    }

    /**
     * 生成日志logId
     * @return string
     */
    public static function logId()
    {
        if(!defined('logId')){
            $logId = date('YmdHis').uniqid();
            define('logId',$logId);
        }
        return logId;
    }

    /**
     * 记录db操作日志
     */
    public static function recordDBlLog(){
        DB::listen(function ($query) {
            $tmp = str_replace('?', '"' . '%s' . '"', $query->sql);
            $sql = vsprintf($tmp, $query->bindings);
            $sql = str_replace("\\", "", $sql);
            self::write('mysql',$sql);
        });
    }

}
