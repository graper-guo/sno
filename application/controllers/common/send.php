<?php
/**
 * 控制器示例
 * Created by PhpStorm.
 * User: baiyan
 * Date: 2018-11-28
 * Time: 15:37
 */

use Nos\Http\Request;
use Nos\Http\Response;
use Nos\Comm\Validator;
use Qcloud\Sms\SmsSingleSender;
use Nos\Comm\Log;
use Nos\Comm\Redis;
use Yaf\Config\Ini;

class Common_SendController extends BaseController
{
    const REDIS_SMS_VERIFY = 'rent_sms_verify_%s';

    /**
     * 是否需要登录授权
     * @var bool
     */
    public $needAuth = false;

    /*
     * 当前登录用户
     */
    public $user;

    /*
     * 模型
     */
    private $testModel;

    /*
     * 参数校验
     */
    public function checkParam()
    {
        Validator::make($this->params = Request::all(), array(
            'phone' => 'required',
        ));
        $this->params['phone'] = Request::get('phone');//获取get参数
    }

    /**
     * 加载模型
     */
    public function loadModel()
    {
    }

    /**
     * 业务逻辑
     */
    public function indexAction()
    {
        $phoneNumbers = explode(',', $this->params['phone']);
        $config = new Ini(APP_PATH . '/config/sms.ini', ini_get('yaf.environ'));
        $config = $config->toArray();

        try {
            $ssender = new SmsSingleSender($config['appId'], $config['appKey']);
            //$code = rand(1000, 9999);
            $code = "1234";
            $params = [$code, "1"];//数组具体的元素个数和模板中变量个数必须一致，例如事例中 templateId:5678对应一个变量，参数数组中元素个数也必须是一个
            $result = $ssender->sendWithParam("86", $phoneNumbers[0], $config['templateId'], $params,'' , "", "");  // 签名参数未提供或者为空时，会使用默认签名发送短信

            $key = sprintf(self::REDIS_SMS_VERIFY, $phoneNumbers[0]);
            Redis::set($key, $code, 6100);
            $rsp = json_decode($result, true);

            Response::apiResponse($rsp['result'], $rsp['errmsg']);
        } catch (\Exception $e) {
            Log::notice("send sms throw a exception!!!".json_encode($phoneNumbers));
            Response::apiResponse($rsp['result'], $rsp['errmsg'], $phoneNumbers);
        }
    }
}