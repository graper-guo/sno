<?php
/**
 * Created by PhpStorm.
 * User: guobutao001
 * Date: 2019/1/29
 * Time: 10:49
 */


namespace Wx;

use Nos\Http\Request;
use Nos\Comm\Config;
use Nos\Comm\Redis;
use Nos\Comm\Log;
use Yansongda\Pay\Pay;
use Nos\Exception\OperateFailedException;
use Yaf\Config\Ini;

class Wx{

    const REDIS_ACCESS_TOKEN_KEY = 'graoe_access_token';
    const MODEL_RELEASE_ORDER = 1;

    private static $config;


    /**
     * 获取wx配置
     * @return mixed|string
     * @throws \Nos\Exception\CoreException
     */
    public function getConfig(){
        if(!isset(self::$config)){
            self::$config = Config::get("wx");
        }
        return self::$config;
    }

    public static function getOpenid($code){
        $config = new Ini(APP_PATH . '/config/wx.ini', ini_get('yaf.environ'));
        $config = $config->toArray();
        $appId = $config['PAY']['APP_ID'];
        $appKey = $config['PAY']['APP_KEY'];

        $url =  "https://api.weixin.qq.com/sns/jscode2session?appid=$appId&secret=$appKey&js_code=$code&grant_type=authorization_code";
        $res = Request::send('GET',$url);

        $res = json_decode($res,true);
        if (array_key_exists('errmsg',$res)){
            Log::notice('wx|get_openid_from_api_failed|msg:' . json_encode($res));
            throw new OperateFailedException('获取微信授权失败');
        }
        return $res['openid'];
    }

    public static function getAccessToken(){
        $acessToken = Redis::get(self::REDIS_ACCESS_TOKEN_KEY);
        if(!empty($acessToken)){
            return $acessToken;
        }
        $config = self::getConfig();
        $appKey = $config['APP_KEY'];
        $appId  = $config['APP_ID'];

        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_typeF=client_credential&appid=$appId&secret=$appKey";
        $res = Request::send('GET',$url);
        $res = json_decode($res);
        if(isset($res['errmsg'])){
            Log::fatal('wx|get_access_token_failed|msg:' . json_encode($res));
            throw new OperateFailedException('获取access_token失败');
        }
        $acessToken = $res['access_token'];
        $exipre = $res['expires_in'];
        Redis::set(self::REDIS_ACCESS_TOKEN_KEY,$acessToken,$exipre);
        return $acessToken;
    }

    public static function sendModelInfo($openid, $modelNum, $params = array()){
        $accessToken = self::getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=$accessToken";
        $config = self::$config;
        switch ($modelNum){
            case self::MODEL_RELEASE_ORDER:
                $config = $config['MODEL_RELEASE_ORDER'];
                $config['touser'] = $openid;
                $config['form_id'] = $params['form_id'];
                $config['data']['keyword1']['value'] = $params['uuid'];
                $config['data']['keyword2']['value'] = $params['created_at'];
                $config['data']['keyword3']['value'] = $params['type'];
                $config['data']['keyword4']['value'] = $params['title'];
                $config['data']['keyword5']['value'] = $params['price'] . '元';
                break;
        }
        $res = Request::send('POST', $url, json_encode($config), array(
            CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($config)
            )
        ));
        $res = json_decode($res, true);
        if (isset($res['errmsg'])){
            Log::fatal('wx|send_model_info_failed|msg:' . json_encode($res));
            throw new OperateFailedException('模板消息发送失败');
        }
        return true;
    }

    /**
     * 获取支付实例
     * @return \Yansongda\Pay\Gateways\Wechat
     * @throws \Nos\Exception\CoreException
     */
    public static function getWxPayApp(){
        $config = self::getConfig();
        $config = $config['PAY'];
        return Pay::wechat($config);
    }
 }