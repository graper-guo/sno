<?php

use Nos\Http\Response;
use Nos\Comm\Redis;

class User_GetLikeController extends BaseController
{

    public  $needAuth = true;

    const LIKE_KEY = "like_key";
    public function checkParam()
    {
    }


    public function indexAction()
    {
        $data = Redis::get(self::LIKE_KEY);
        $data = json_decode($data);
        Response::apiSuccess($data);
    }
}