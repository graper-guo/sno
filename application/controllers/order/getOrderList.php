<?php
/**
 * Created by PhpStorm.
 * User: guobutao001
 * Date: 2019/2/11
 * Time: 14:16
 */

use Nos\Http\Request;
use Nos\Http\Response;
use Nos\Comm\Page;

class Order_GetOrderListController extends BaseController
{

    public $needAuth = true;
    private $orderModel;
    private $userModel;

    public function checkParam()
    {
        $page = Request::get('page');
        $size = Request::get('size');
        $this->params['page'] = !empty($page) ? $page : 1;
        $this->params['size'] = !empty($size) ? $size : 10;
    }

    public function loadModel()
    {
        $this->orderModel = new Order_OrderModel();
        $this->userModel = new User_UserModel();
    }

    public function indexAction()
    {
        $size = $this->params['size'];

        $offset = Page::getLimitData($this->params['page'],$size);
        $now = date('Y-m-d H:i:s');
        $select = array('id','title','status','content','price','utime','ctime','longitude','latitude','renter','hirer','is_rent');

        if (!isset($this->params['type'])){
            $ext = "where status = ? and begin < ? and end > ? order by ctime desc limit {$offset},{$size}";
            $bind = array(Order_OrderModel::STATUS_RELEASED, $now, $now);
        } else{
            $ext = "where status = ? and class = ? and begin < ? and end > ? order by ctime desc limit {$offset},{$size}";
            $bind = array(Order_OrderModel::STATUS_RELEASED, $this->params['type'], $now, $now);
        }

        $orders = $this->orderModel->getList($select,$ext,$bind);

        if(empty($orders)){
            Response::apiSuccess();
        }
        $count = count($orders);

        foreach ($orders as &$v) {
            $v['content'] = $this->limit($v['content'], 100, '...');
            if (!empty($v['renter'])) {
                $sender = $this->userModel->getById($v['renter'], array('avatar'));
                $v['renter_avatar'] = $sender['avatar'];
            }elseif (!empty($v['hirer'])) {
                $sender = $this->userModel->getById($v['hirer'], array('avatar'));
                $v['renter_avatar'] = $sender['avatar'];
            }
            unset($v['renter']);
        }

        $curLng = $this->params['longitude'];
        $curLat = $this->params['latitude'];
        foreach ($orders as $k => $order){
            isset($order['longitude']) && $orderLng = $order['longitude'];
            isset($order['latitude']) && $orderLat = $order['latitude'];
            if (!isset($orderLng) | !isset($orderLat)){
                continue;
            }
            $orders[$k]['distance'] = $this->orderModel->getDistance($curLng, $curLat, $orderLng, $orderLat);
        }

        $pageData = Page::paginate($count, $this->params['page'], $size);
        Response::apiSuccess(array_merge(array('data' => $orders), $pageData));
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