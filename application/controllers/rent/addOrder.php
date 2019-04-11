<?php
/**
 * 发布出租需求
 * Created by PhpStorm.
 * User: guobutao001
 * Date: 2019/3/1
 * Time: 10:55
 */

use Nos\Comm\Log;
use Nos\Comm\Validator;
use Nos\Exception\OperateFailedException;
use Nos\Exception\ParamValidateFailedException;
use Nos\Http\Request;
use Nos\Http\Response;

class Rent_AddOrderController extends BaseController{
    public $needAuth = true;
    public $user;
    private $orderModel;
    public function checkParam()
    {
        Validator::make($this->params = Request::all(),array(
            'title' => 'required',
            'content' => 'required',
            'beginTime' => 'required|dateTime',
            'endTime' => 'required|dateTime',
            'type' => 'required',
            'types' => 'required',
            'price' => 'required',
            'longitude' => 'required',
            'latitude' => 'required'
        ));
        if (strtotime($this->params['beginTime']) > strtotime($this->params['endTime'])){
            throw new ParamValidateFailedException('起止日期时间不合法');
        }
    }
    public function loadModel()
    {
        $this->orderModel = new Order_OrderModel();
    }
    public function indexAction()
    {
        $data = array();
        $data['title'] = $this->params['title'];
        $data['content'] = $this->params['content'];
        $data['begin'] = $this->params['beginTime'];
        $data['end'] = $this->params['endTime'];
        $data['class'] = $this->params['type'];
        $data['is_rent'] = $this->params['types'];
        $data['status'] = Order_OrderModel::STATUS_RELEASED;
        $data['price'] = $this->params['price'];
        $data['longitude'] = $this->params['longitude'];
        $data['latitude'] = $this->params['latitude'];
        if($data['is_rent'] === 1) {
            $data['hirer'] = $this->user->id;
        }else{
            $data['renter'] = $this->user->id;
        }

        $time = date('Y-m-d H:i:s');
        $data['ctime'] = $time;
        $rows = $this->orderModel->create($data);
        if (!$rows){
            Log::fatal('ask|insert_into_orders_failed|data:' . json_encode($data));
            throw new OperateFailedException('新订单创建失败，请重试');
        }
        Response::apiSuccess(array('id' => $rows));
    }
}