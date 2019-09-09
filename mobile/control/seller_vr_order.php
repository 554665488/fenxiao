<?php
/**
 * 兑换码验证 v5.6
 *
 * @好商城 (c) 2015-2018 33HAO Inc. (http://www.33hao.com)
 * @license    http://www.33 hao.c om
 * @link       交流群号：138182377
 * @since      好商城提供技术支持 授权请购买shopnc授权
 */



defined('In33hao') or exit('Access Invalid!');


class seller_vr_orderControl extends mobileHomeControl {

    public function __construct(){
        parent::__construct();
    }


    /**
     * 兑换码消费
     */
    public function exchangeOp() {

        if ($_GET['vr_code']) {
            if (!preg_match('/^[a-zA-Z0-9]{15,18}$/',$_GET['vr_code'])) {

                output_error('兑换码格式不正确');
            }
            $model_vr_order = Model('vr_order');
            $vr_code_info = $model_vr_order->getOrderCodeInfo(array('vr_code' => $_GET['vr_code']));
            
            if (empty($vr_code_info)) {
                output_error('该兑换码不存在');
            }
            if ($vr_code_info['vr_state'] == '1') {
                output_error('该兑换码已被使用');
            }
            if ($vr_code_info['vr_indate'] < TIMESTAMP) {
                output_error('该兑换码已过期，使用截止日期为： '.date('Y-m-d H:i:s',$vr_code_info['vr_indate']));
            }
            if ($vr_code_info['refund_lock'] > 0) {//退款锁定状态:0为正常,1为锁定(待审核),2为同意
                output_error('该兑换码已申请退款，不能使用');
            }

            //更新兑换码状态
            $update = array();
            $update['vr_state'] = 1;
            $update['vr_usetime'] = TIMESTAMP;
            $update = $model_vr_order->editOrderCode($update, array('vr_code' => $_GET['vr_code']));
            
            //如果全部兑换完成，更新订单状态
            Logic('vr_order')->changeOrderStateSuccess($vr_code_info['order_id']);
            


            if ($update) {
                //取得返回信息
                $order_info = $model_vr_order->getOrderInfo(array('order_id'=>$vr_code_info['order_id']));
                if ($order_info['use_state'] == '0') {
                    $model_vr_order->editOrder(array('use_state' => 1), array('order_id' => $vr_code_info['order_id']));
                }
                $order_info['img_60'] = thumb($order_info,60);
                $order_info['img_240'] = thumb($order_info,240);
                $order_info['goods_url'] = urlShop('goods','index',array('goods_id' => $order_info['goods_id']));
                $order_info['order_url'] = urlShop('store_vr_order','show_order',array('order_id' => $order_info['order_id']));

                output_data($order_info);
            }
        } else {
            output_error('兑换码信息错误');
        }
    }

}
