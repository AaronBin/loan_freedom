<?php namespace app\libs;
/**
 * Created by PhpStorm.
 * User: zhaobin
 * Date: 17/10/25
 * Time: 11:21
 * 公共单例实现工厂
 * 调用方式   Factory:getInstance(ClassName:class)->method();
 */

class Factory{

    protected static $_instance = [];

    public static function __callStatic($name,$params)
    {
        $class_name = explode("\\",$params[0]);

        if(isset(self::$_instance[end($class_name)]))
        {
            return self::$_instance[end($class_name)];
        }
        return self::$_instance[end($class_name)] = new $params[0];
    }
}