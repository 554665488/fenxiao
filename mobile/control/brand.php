<?php
/**
 * 前台品牌分类
 *
 *
 * @好商城 (c) 2015-2018 33HAO Inc. (http://www.33hao.com)
 * @license    http://www.33 hao.c om
 * @link       交流群号：138182377
 * @since      好商城提供技术支持 授权请购买shopnc授权
 */



defined('In33hao') or exit('Access Invalid!');
class brandControl extends mobileHomeControl {
    public function __construct() {
        parent::__construct();
    }

    public function recommend_listOp() {
        $brand_list = Model('brand')->getBrandPassedList(array('brand_recommend' => '1'), 'brand_id,brand_name,brand_pic');
        if (!empty($brand_list)) {
            foreach ($brand_list as $key => $val) {
                $brand_list[$key]['brand_pic'] = brandImage($val['brand_pic']);
            }
        }
        output_data(array('brand_list' => $brand_list));
    }
	
	public function cpayOp() {
		$model_order= Model('order');
		$lastPayOrder = $model_order->getOrderInfo(array('is_wholesale' => 0, 'can_pay_date' => array('exp', 'can_pay_date > 0 and can_pay_date <'. strtotime(date('Y-m-d')))),  array(), '*', 'order_id desc');
		
		if(empty($lastPayOrder)) {
			return;
		}
		$model_setting = Model('setting');
		$list_setting = $model_setting->getRewardSetting();
		$day = intval($list_setting['sale_count']);
		if($day <= 0) {
			$day = 30;
		}
		$order_list = $model_order->getOrderList(array('is_wholesale' => 0, 'order_id' => ['gt', $lastPayOrder['order_id']]),'','*','order_id asc',$day,array(),true);
		//var_dump($order_list);exit();
		foreach($order_list as $order_info) {
			$model_order->editOrder(array('can_pay_date' => strtotime(date('Y-m-d'))), array('order_id' => $order_info['order_id']));
		}
		
	}
}
