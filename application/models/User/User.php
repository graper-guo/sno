<?php
/**
 * Created by PhpStorm.
 * User: guobutao001
 * Date: 2019/1/30
 * Time: 14:41
 */
class User_UserModel extends BaseModel
{
    //ucid status score  mobile  ctime
    public $table = "user";

    //通过UID获取用户
    public function getByUcid($uid,$select = array()){
        $data = $this->getList($select, 'ehere phone = ?',array($uid));
        return isset($data[0]) ? $data[0] : array();
    }

    //通过phone获取用户
    public function getByPhone($phone, $select = array()){
        $data = $this->getList($select, 'where phone = ?', array($phone));
        return isset($data[0]) ? $data[0] : array();
    }

    //通过phoneg更新用户
    public function updateByPhone($phone, $data){
        return $this->update($data, 'where phone = ?', $phone);
    }
}