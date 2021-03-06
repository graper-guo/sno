<?php
/**
 * 异常处理类
 * Created by PhpStorm.
 * User: baiyan
 * Date: 2018-11-28
 * Time: 11:21
 */

use Nos\Comm\Log;
use Nos\Http\Request;
use Nos\Http\Response;
use Yaf\Controller_Abstract;

class ErrorController extends Controller_Abstract {

    /**
     * 异常处理入口
     * @param $exception
     */
    public function errorAction($exception){
        $code = $exception->getCode();
        $msg = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        Log::fatal(array(
            'status' => $code,
            'msg'    => $msg,
            'file'   => $file,
            'line'   => $line,
            'url'    => $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
            'method' => $_SERVER['REQUEST_METHOD'],
            'params' => Request::all()
        ));
        Response::apiResponse($code, $msg);
    }

}