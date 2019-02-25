<?php
/**
 * Created by PhpStorm.
 * User: guobutao001
 * Date: 2018/11/30
 * Time: 10:00
 */
/**
 * 控制器公共基类
 * Created by PhpStorm.
 * User: baiyan
 * Date: 2018-11-27
 * Time: 17:21
 */

use Yaf\Controller_Abstract;
use Firebase\JWT\JWT;
use Nos\Comm\Config;
use Nos\Comm\Log;
use Nos\Http\Request;
use Nos\Comm\Redis;
use Nos\Http\Response;

abstract class BaseController extends Controller_Abstract{

    /**
     * 当前登录用户
     * @var null
     */
    protected $user = null;

    /**
     * 请求参数
     *
     * @var array
     */
    protected $params = array();

    /**
     * 返回数据
     *
     * @var array
     */
    protected $output = array();

    /**
     * 是否需要校验
     *
     * @var bool
     */
    protected $needAuth;//是否需要校验

    const REDIS_TOKEN_PREFIX = 'rent_token_%s';
    /**
     * 初始化
     */
    private function init(){
        $this->needAuth && $this->user = $this->auth();
        $this->checkParam();//请求参数校验
        $this->loadModel();//模型载入
    }

    /**
     * 用户授权并返回当前用户对象，自动载入到控制器$user变量中
     * @return object
     */
    protected function auth(){
        $frontToken = Request::header('Authorization');
        if (empty($frontToken)) {
            Log::notice('auth|header_token_empty');
            Response::apiUnauthorized();
        }
        try{
            $key = Config::get('common.JWT');
            $user = JWT::decode($frontToken, $key ,['HS256']);
        }catch (\Exception $e){
            Log::notice('auth|decode_token_failed|msg:' . $e->getMessage() . '|frontToken:'. $frontToken);
            Response::apiUnauthorized();
        }
        $redisKey = sprintf(self::REDIS_TOKEN_PREFIX, $user->ucid);
        $token = Redis::get($redisKey);//查redis里token，比较
        if ($frontToken !== $token) {
            Log::notice('auth|front_token_not_equals_redis_token|front_token:' . $frontToken . '|redis_token:' . $token);
            Response::apiUnauthorized();
        }
        return $user;
    }

    /**
     * 参数校验
     */
    abstract protected function checkParam();

    /**
     * 业务逻辑
     */
    abstract protected function indexAction();

    /**
     * 加载模型
     */
    protected function loadModel(){}

}