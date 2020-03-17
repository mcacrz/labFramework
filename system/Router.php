<?php
namespace System;

use Controllers;

class Router{
    private static $path;
    private static $httpMethod;

    public static function get($path, $callback){
        self::setPath();
        self::setHttpMethod();

        return (self::$httpMethod !== 'GET')
        ?: self::execAction($path,$callback);
    }

    public static function post($path,$callback){
        self::setPath();
        self::setHttpMethod();

        return (self::$httpMethod !== 'POST')
        ?: self::execAction($path,$callback);
    }

    private static function formatAction($stringCallback)
    {
        $arrayParams = explode('@',$stringCallback);
        list($className,$methodName) = $arrayParams;
        $classPath = 'Controllers\\'.$className;
        $class = new $classPath();
        return [$class,$methodName];
    }

    private static function setPath()
    {
        self::$path = str_replace('public/','',str_replace('favicon.ico','',$_SERVER['REQUEST_URI']));
    }

    private static function setHttpMethod()
    {
        self::$httpMethod =  filter_input(INPUT_SERVER,'REQUEST_METHOD');
    }

    private static function execAction($path,$callback)
    {
        if(self::$path === $path){
            if(is_callable($callback)){
                return call_user_func_array($callback,[]);
            }
    
            if(is_string($callback)){
                list($classPath,$methodName) = self::formatAction($callback);
                return call_user_func_array([$classPath,$methodName],[]);
            }    
        }        
    }
}