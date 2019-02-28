<?php
/**
 * Created by PhpStorm.
 * User: guobutao001
 * Date: 2019/2/25
 * Time: 17:12
 */

use Nos\Comm\Log;
use Nos\Comm\Validator;
use Nos\Exception\OperateFailedException;
use Nos\Http\Response;
use Wx\Wx;

class Pay_TransferController extends BaseController{
    private $orderModel;
    private $userModel;
    public function loadModel()
    {
        $this->orderModel = new Order_OrderModel();
        $this->userModel = new User_UserModel();
    }
    public function checkParam()
    {
        Validator::make($this->params,array(
            'order_id' => 'required'
        ));
    }
    public function indexAction()
    {
        $order = $this->orderModel->getByOrderId($this->params['order_id']);
        if ($order->status != Order_OrderModel::STATUS_WAITING_COMMENT){
            Log::notice('wxpay|wrong_order_status|order:' . json_encode($order));
            throw new OperateFailedException('错误的订单状态');
        }
        $receiver = $this->userModel->getByUcid($order['receiver_id']);
        if (empty($receiver['openid']) || empty($order['ucid']) || empty($order['price']) || empty($order['title'])){
            Log::notice('wxpay|transfer_params_error');
            throw new OperateFailedException('转账参数不正确');
        }
        $params = [
            'partner_trade_no' => $order['order_id'],              //商户订单号
            'openid' => $receiver['openid'],        //收款人的openid
            'check_name' => 'NO_CHECK',                //NO_CHECK：不校验真实姓名\FORCE_CHECK：强校验真实姓名
            'amount' => intval($order['price']) * 100,         //企业付款金额，单位为分
            'desc' => $order['title'],                   //付款说明
            'type' => 'miniapp'
        ];
        $app = Wx::getWxPayApp();
        Log::notice('wxpay|wxtransfer_pay_params:' . json_encode($params));
        try{
            $res = $app->transfer($params);
        } catch (\Exception $e){
            Log::fatal('wxpay|wxtransfer_error:' . json_encode($e->getMessage()));
            throw new OperateFailedException('调用转账接口失败');
        }
        Log::notice('wxpay|wxtransfer_pay_res:|res:' . json_encode($res));
        Response::apiSuccess($res);
    }
}