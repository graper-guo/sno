<?php

use Nos\Http\Request;
use Nos\Comm\Validator;
use Nos\Http\Response;

class User_GetUserInfoController extends BaseController
{

    public  $needAuth = true;

    public function checkParam()
    {
    }


    public function indexAction()
    {
        Response::apiSuccess($this->user);
    }
}