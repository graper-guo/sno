<?php

use Nos\Http\Request;
use Nos\Comm\Validator;
use Nos\Http\Response;
use Nos\Comm\Redis;

class User_LikeController extends BaseController
{

    public  $needAuth = false;

    const LIKE_KEY = "like_key";
    public function checkParam()
    {
        Validator::make($this->params = Request::all(), array(
            'like' => 'required',
            'count' => 'required',
        ));
    }


    public function indexAction()
    {
        $like = $this->params['like'];
        $count = $this->params['count'];
        $data = [
            'like' => $like,
            'count' => $count
        ];
        Redis::set(self::LIKE_KEY, json_encode($data));
        Response::apiSuccess();
    }
}