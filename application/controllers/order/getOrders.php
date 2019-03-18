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

        $offset = Page::getLimitData($this->params['page'],$size);
        $select = array('id','title','status','content','price','utime','renter');
        $text1 = "where class = ? and deleted is null order by utime desc limit {$offset},{$size}";

        $orders = $this->orderModel->getList($select,$text1,array($type));

        $ext2 = "where class = ? and deleted is null";
        $count = $this->orderModel->getTotal($ext2, array($type));

        foreach ($orders as &$v) {
            $v['content'] = $this->limit($v['content'], 100, '...');
            if (!empty($v['renter'])) {
                $sender = $this->userModel->getById($v['renter'], array('avatar'));
                $v['renter_avatar'] = $sender['avatar'];
            }
            unset($v['renter']);
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