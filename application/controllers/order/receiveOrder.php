<?php
/**
 * Created by PhpStorm.
 * User: guobutao001
 * Date: 2019/2/11
 * Time: 14:16
 */

use Nos\Http\Request;
use Nos\Http\Response;
use Nos\Exception\OperateFailedException;
use Nos\Comm\Log;

class Order_ReceiveOrderController extends BaseController
{

    public $needAuth = true;
    private $orderModel;
    public $user;

    public function checkParam()
    {
        $id = Request::get('id');

        $this->params['id'] = $id;
    }

    public function loadModel()
    {
        $this->orderModel = new Order_OrderModel();
    }

    public function indexAction()
    {
        $id = $this->params['id'];

        $ucid = $this->user->id;
        $order = $this->orderModel->getById($id);
        if($order['is_rent'] == 0) {
            if ($order['status'] != Order_OrderModel::STATUS_RELEASED) {
                Log::notice('ho|wrong_order_status|order:' . json_encode($order));
                throw new OperateFailedException('错误的订单状态');
            }
            if ($order['renter'] == $ucid) {
                Log::notice('ho|can_not_receive_own_order|order:' . json_encode($order));
                throw new OperateFailedException('您不能接自己发布的订单');
            }

            $data = [
                'hirer' => $ucid,
                'status' => Order_OrderModel::STATUS_RUNNING,
            ];
            $res = $this->orderModel->update($data, 'where id = ?', $id);
        }else {
            if ($order['status'] != Order_OrderModel::STATUS_RELEASED) {
                Log::notice('ho|wrong_order_status|order:' . json_encode($order));
                throw new OperateFailedException('错误的订单状态');
            }
            if ($order['hirer'] == $ucid) {
                Log::notice('ho|can_not_receive_own_order|order:' . json_encode($order));
                throw new OperateFailedException('您不能接自己发布的订单');
            }

            $data = [
                'renter' => $ucid,
                'status' => Order_OrderModel::STATUS_RUNNING,
            ];
            $res = $this->orderModel->update($data, 'where id = ?', $id);
        }
        Response::apiSuccess($res);
    }

    /**
     * 裁剪字符串
     * @param $value
     * @param int $limit
     * @param string $end
     * @return string
     */
    private function limit($value, $limit = 100, $end = '...')
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }
        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')) . $end;
    }
}