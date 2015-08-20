<?php
namespace Home\Controller;
use Think\Controller;
class OtherController extends Controller {

    /**
     * 求二维数组某个字段和
     * @param $arr array 二维数组
     * @param $field string 求和的字段
     * @return int
     */
    function sum($arr ,$field){
        if(!is_array(current($arr))) //非二维数组
            return 0;

        $list = array();
        foreach($arr as $v)
            $list[] = $v[$field];
        return array_sum($list);
    }

}