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
use Nos\Comm\Log;
use Nos\Comm\Redis;
use Yaf\Config\Ini;
use Nos\Exception\OperateFailedException;
use Wx\Wx;
use Nos\Comm\Config;
use Firebase\JWT\JWT;

class Common_LoginController extends BaseController
{
    const REDIS_SMS_VERIFY = 'rent_sms_verify_%s';
    const REDIS_TOKEN_PREFIX = "rent_token_%s";

    /**
     * 是否需要登录授权
     * @var bool
     */
    public $needAuth = true;

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
            'code' => 'required',
            'wxCode' => 'required',
            'avatar' => 'required',
            'nickname' => 'required',

        ));
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
        $phone = $this->params['phone'];
        $frontCode = $this->params['code'];
        $key = sprintf(self::REDIS_SMS_VERIFY,$phone);
        $backCode = Redis::set($key);
        if($frontCode != $backCode){
            Log::notice('sms_error_code'.$key.'frontCode:'.$frontCode.',backCode:',$backCode);
            throw new OperateFailedException("短信验证码验证错误，请重试！");
        }
        $openId = Wx::getOpenid($this->params['wxCode']);
        $data = [
          'phone' => $phone,
          'name'  => $this->params['nickname'],
          'avatar' => $this->params['avatar'],
          'openid' => $openId
        ];
        $user = $this->getLatestUser($data);
        $token = $this->setToken($user);
        Response::apiSuccess(array('token' => $token));
    }

    private function setToken($data){
        $key = Config::get('common.JWT');
        $token = JWT::encode($data,$key);
        $redisKey = sprintf(self::REDIS_TOKEN_PREFIX, $data['ucid']);
        Redis::set($redisKey, $token, 2678400);
        return $token;
    }

    private function getLatestUser($data){
        $userModel = new User_UserModel();
        $user = $userModel->getByPhone($data['phone']);
        if(!$user){
            $user->creat($data);
        }else{
            $userModel->updateByPhone($data['phone'], $data);
            $user = $userModel->getByphone($data['phone']);
        }
        return $user;
    }
}