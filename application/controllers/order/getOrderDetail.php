<?php
/**
 * Created by PhpStorm.
 * User: guobutao001
 * Date: 2019/2/11
 * Time: 14:12
 */

use Nos\Http\Request;
use Nos\Comm\Validator;
use Nos\Http\Response;

class Order_GetOrderDetailController extends BaseController
{

    public $needAuth = true;
    private $orderModel;
    private $userModel;

    public function checkParam()
    {
        Validator::make($this->params = Request::all(), array(
            'id' => 'required',
        ));
    }

    public function loadModel()
    {
        $this->orderModel = new Order_OrderModel();
        $this->userModel = new User_UserModel();
    }

    public function indexAction()
    {
        $order = $this->orderModel->getById($this->params['id']);
        $renter = $this->userModel->getById($order['renter']);
        $hirer = $this->userModel->getById($order['hirer']);
        Response::apiSuccess(array_merge($order, array(
            'renter' => $renter,
            'hirer' => $hirer,
        )));
    }
}