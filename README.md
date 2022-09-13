## 1.基本介绍
### 1.1 项目介绍
> 基于laravel框架的日志，可以记录输入、输出日志、mysql操作日志，通过logid查询一次请求的所有日志
### 1.2 配置
在laravel的 app\Providers\AppServiceProvider.php添加如下代码
记录mysql操作日志
```
    public function boot()
    {
        MyLogs::dBLog(DB::class);
    }
```

新建app\Http\Middleware\Logs.php,记录请求、返回日志

```
<?php

namespace App\Http\Middleware;

use Closure;
use Mjy191\MyLogs\MyLogs;

class Logs
{
    /**
     * 返回参数值记录post请求，get请求不记录
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        MyLogs::reqLog($request);
        $response = $next($request);
        MyLogs::resLog($request,$response);
        return $response;
    }
}
```

### 1.3. 请求日志查询
日志均保存在logs目录下
通过logid 20220913152544632030780f64a 查询
```$xslt
grep 20220913152544632030780f64a *
2022-09-13 15:25:44 uri[/admin/test/index] cgi[cli-server] logId[20220913152544632030780f64a]  request[{"userName":"1111","password":"122333"}]
2022-09-13 15:25:44 uri[/admin/test/index] cgi[cli-server] logId[20220913152544632030780f64a]  mysql[select * from `user` where `id` = "1" limit 1]
2022-09-13 15:25:44 uri[/admin/test/index] cgi[cli-server] logId[20220913152544632030780f64a]  response[{"code":1,"msg":"success","data":{"id":1,"userName":"aa"},"timestamp":1663053944}]
```

### 1.4 安装
```
composer require mjy191/my-logs
```
