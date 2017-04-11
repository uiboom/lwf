<?php

/**
 * Created by PhpStorm.
 * User: lejianwen
 * Date: 2017/3/13
 * Time: 16:03
 * QQ: 84855512
 */
use Illuminate\Database\Capsule\Manager as Capsule;

class bootstrap
{
    /**server
     * @var \swoole_websocket_server
     */
    public static $server;

    public static function start()
    {
        self::init();
        //数据库配置载入
        self::database();
    }

    public static function init()
    {
        //默认时区定义
        date_default_timezone_set('Asia/Shanghai');
        //设置默认区域
        setlocale(LC_ALL, "zh_CN.utf-8");
        //关闭错误报告
        error_reporting(0);
        //设置根路径
        defined('BASE_PATH') or define('BASE_PATH', __DIR__ . '/../');
        //系统日志路径
        defined('SYSTEM_LOG_PATH') or define('SYSTEM_LOG_PATH', __DIR__ . '/../runtime/log/system/');
        //配置文件路径
        defined('CONFIG_PATH') or define('CONFIG_PATH', BASE_PATH . 'config/');
        //配置文件是否静态加载
        defined('CONFIG_STATIC') or define('CONFIG_STATIC', false);
    }

    //orm 模型
    public static function database()
    {
        // Create a new Database connection
        $capsule = new Capsule;
        $capsule->addConnection(require CONFIG_PATH . 'database.php');
        $capsule->bootEloquent();
    }

    /**获取路由
     * @return null
     */
    public static function router()
    {
        if (CONFIG_STATIC)
        {
            static $routers;
            //静态配置路由
            if (!$routers)
                $routers = config('routers');
        } else
        {
            $routers = config('routers');
        }
        return $routers;
    }

    /**解析并运行
     * @param \swoole_websocket_frame $frame
     */
    public static function dispatch(\swoole_websocket_frame $frame)
    {
        $routers = self::router();
        $router_uri = array_keys($routers);
        $data = \lib\traits\message::decode($frame->data);
        if (!in_array($data['uri'], $router_uri))
            return;
        list($controller, $action) = explode('@', $routers[$data['uri']]);
        $class = 'app\\controllers\\' . $controller;
        $controller = new $class($frame);
        $controller->$action();
        unset($controller);
        unset($class);
    }

    /**接收并分配任务
     * @param $data
     */
    public static function task($data)
    {
        list($task, $action) = explode('@', $data['task']);
        $class = 'app\\tasks\\' . $task;
        if(method_exists($class, $action))
        {
            $task = new $class($data);
            $task->$action();
            unset($task);
        }
    }

    /**连接打开时会调用此方法
     * @param $server
     * @param $request
     */
    public static function serverOpen($server, $request)
    {
        if($params = $request->get)
        {
        }
    }

    /**连接关闭时会调用此方法
     * @param $server
     * @param $fd
     */
    public static function serverClose($server, $fd)
    {

    }
}