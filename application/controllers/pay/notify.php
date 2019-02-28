<?php
/**
 * Created by PhpStorm.
 * User: guobutao001
 * Date: 2019/2/25
 * Time: 15:33
 */


use Wx\Wx;
use Nos\Comm\Log;
use Nos\Exception\OperateFailedException;

class Pay_NotifyController extends BaseController{
    public $needAuth = true;
    private $orderModel;
    public $user;
    /**
     * 参数校验
     */
    public function checkParam()
    {
    }

    /**
     * 业务逻辑
     */
    public function indexAction()
    {
        try{
            $app = Wx::getWxPayApp();
            $app->verify();
            return $app->success();
        } catch (\Exception $e){
            Log::notice('wxpay| notify_failed| msg:' . json_encode($e->getMessage()));
            throw new OperateFailedException($e->getMessage());
        }
    }
}