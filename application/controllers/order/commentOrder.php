<?php
/**
 * Created by PhpStorm.
 * User: guobutao001
 * Date: 2019/2/11
 * Time: 11:24
 */

use Nos\Comm\Log;
use Nos\Http\Response;
use Nos\Exception\OperateFailedException;
use Nos\Http\Request;
use Nos\Comm\Validator;

class Order_CommentOrderController extends BaseController
{

    public $needAuth = true;
    private $orderModel;
    private $userModel;
    const SCORE = 5;

    public function checkParam()
    {
        Validator::make($this->params = Request::all(), array(
            'id' => 'required',
            'comm' => 'required',
            'ucid' => 'required',
        ));
    }

    public function loadModel()
    {
        $this->orderModel = new Order_OrderModel();
        $this->userModel = new User_UserModel();
    }

    public function indexAction()
    {
        $order = $this->orderModel->getByUcid($this->params['id']);
        if ($order['status'] != Order_OrderModel::STATUS_WAITING_COMMENT) {
            Log::notice('order|wrong_order_status|order:' . json_encode($order));
            throw new OperateFailedException('错误的订单状态');
        }
        if ($order['hirer'] != $this->params['ucid']) {
            Log::notice('order|wrong_user_deal_status|order:' . json_encode($order));
            throw new OperateFailedException('用户没有权限处理该订单');
        }
        $this->orderModel->update(array(
            'status' => Order_OrderModel::STATUS_FINISHED,
            'comment' => $this->params['comm'],
        ), 'where id = ?', array($this->params['id']));

        $hirer = $this->userModel->getByUid($order['hirer']);
        $this->userModel->update(array(
            'score' => $hirer['score'] + self::SCORE,
        ), 'where ucid = ?', array($order['hirer']));
        Response::apiSuccess();
    }
}