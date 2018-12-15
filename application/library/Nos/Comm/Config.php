<?php
/**
 * 配置操作类
 * Created by PhpStorm.
 * User: baiyan
 * Date: 2018-11-28
 * Time: 10:55
 */

namespace Nos\Comm;

use Nos\Exception\CoreException;
use Yaf\Registry;

class Config{

    /**
     * 获取指定配置项（格式:文件名.key1.key2）
     * @param $key
     * @param string $default
     * @return mixed|string
     * @throws CoreException
     */
    public static function get($key, $default = ''){
        $pathArr = array();
        if (strpos($key, '.') !== false) {
            $pathArr = explode('.',$key);
            $len = count($pathArr);
            if ($len  > 3){
                throw new CoreException('config key over max len');
            }
            $file = $pathArr[0];
        }else{
            $file = $key;
        }
        $file = ROOT_PATH . '/config/' . ucfirst($file) . '.php';
        if (!file_exists($file)){
            throw new CoreException('config file not exists');
        }
        if (!Registry::has($file)){
            $config = include $file;
            Registry::set($file, $config);
        } else{
            $config = Registry::get($file);
            if (empty($config)){
                $config = include $file;
                Registry::set($file, $config);
            }
        }
        if (count($pathArr) == 2){
            $ret = isset($config[$pathArr[1]]) ? $config[$pathArr[1]] : $default;
        } else if (count($pathArr) == 3){
            $ret = isset($config[$pathArr[1]][$pathArr[2]]) ? $config[$pathArr[1]][$pathArr[2]] : $default;
        } else{
            $ret = $config;
        }
        if ($ret == ''){
            return false;
        }
        return isset($ret) ? $ret : $default;
    }

}