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

class Order_GetOrdersController extends BaseController
{

    public $needAuth = true;
    private $orderModel;
    private $userModel;

    public function checkParam()
    {
        $page = Request::get('page');
        $size = Request::get('size');
        $type = Request::get("type");
        $this->params['page'] = !empty($page) ? $page : 1;
        $this->params['size'] = !empty($size) ? $size : 10;
        $this->params['type'] = $type;
    }

    public function loadModel()
    {
        $this->orderModel = new Order_OrderModel();
        $this->userModel = new User_UserModel();
    }

    public function indexAction()
    {
        $size = $this->params['size'];
        $type = $this->params['type'];

        $now = date('Y-m-d H:i:s');
        $offset = Page::getLimitData($this->params['page'],$size);
        $select = array('id','title','status','content','price','utime','ctime','longitude','latitude','renter','hirer','is_rent');
        $text1 = "where status = ? and class = ? and deleted is null and begin < ? and end > ? order by ctime desc limit {$offset},{$size}";
        $bind = array(Order_OrderModel::STATUS_RELEASED, $type , $now, $now);

        $orders = $this->orderModel->getList($select,$text1,$bind);

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