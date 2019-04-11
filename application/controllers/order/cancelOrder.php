<?php
/**
 * Created by PhpStorm.
 * User: guobutao001
 * Date: 2019/3/20
 * Time: 14:36
 */

use Nos\Comm\Validator;
use Nos\Http\Request;
use Nos\Http\Response;

class Order_CancelOrderController extends BaseController{
    public $needAuth = true;
    public $user;

    private $orderModel;
    public function checkParam()
    {
        Validator::make($this->params = Request::all(), array(
            'id' => 'required'
        ));
    }
    public function loadModel()
    {
        $this->orderModel = new Order_OrderModel();
    }
    public function indexAction()
    {
        $id = $this->params['id'];
        $orders = $this->orderModel->getById($id);
        if($orders['status'] != Order_OrderModel::STATUS_RELEASED){
            Response::apiOperateFailed();
        }
        $ext = 'where id = ?';
        //$this->orderModel->delete(true, $ext, array($id),'deleted');
        $this->orderModel->update(array(
            'status' => Order_OrderModel::STATUS_CANCELED,
            'deleted' => 1
        ), $ext, array($id));
        Response::apiSuccess();
    }
}