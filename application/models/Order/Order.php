<?php
/**
 * Created by PhpStorm.
 * User: guobutao001
 * Date: 2019/2/11
 * Time: 11:47
 */

class Order_OrderModel extends BaseModel {
    //id  订单id
    //status  订单状态
    //score  订单积分
    //renter,hirer 出租人,租赁人
    public $table = 'order';
    /**
     * 订单状态
     */
    const
        STATUS_NOT_RELEASED = 0,
        STATUS_RELEASED = 1,//已发布
        STATUS_RUNNING = 2,// 交易进行中
        STATUS_WAITING_COMMENT = 3,//租赁服务完成等待评价
        STATUS_FINISHED = 4,//评价完成
        STATUS_CANCELED = 5;//订单取消
    /**
     * 订单类别
     */
    const
        TYPE_RENT = 0,//出租
        TYPE_HIRE = 1;//租赁

    /**
     * 奖励积分数量
     */
    const
        AWARD_SENDER = 1,
        AWARD_RECEIVER = 5;
    /**
     * 获取两个订单之间的距离
     * @param $lng1
     * @param $lat1
     * @param $lng2
     * @param $lat2
     * @return float|int
     */
    public function getDistance($lng1, $lat1, $lng2, $lat2)
    {
        $lng1 = intval($lng1);
        $lat1 = intval($lat1);
        $lng2 = intval($lng2);
        $lat2 = intval($lat2);
        $radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137;
        return round($s,2) . 'km';
    }
    /**
     * 根据ucid获取订单
     * @param $uuid
     * @param array $select
     * @return array
     * @throws \Nos\Exception\CoreException
     */
    public function getByUcid($ucid, $select = array()){
        $data = $this->getList($select, 'where ucid = ?', array($ucid));
        return isset($data[0]) ? $data[0] : array();
    }
    /**
     * 根据id获取订单
     */
    public function getByOrderId($order_id, $select = array())
    {
        $data = $this->getList($select,'where order_id = ?',array($order_id));
        return isset($data[0]) ? $data[0] : array();

    }
}