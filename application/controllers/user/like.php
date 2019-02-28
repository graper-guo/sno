<?php

use Nos\Http\Request;
use Nos\Comm\Validator;
use Nos\Http\Response;
use Nos\Comm\Redis;

class User_LikeController extends BaseController
{

    public  $needAuth = true;
    public function checkParam()
    {
    }


    public function indexAction()
    {
        $like = $this->params['like'];

        Response::apiSuccess($this->user);
    }
}