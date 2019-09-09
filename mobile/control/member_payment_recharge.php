<?php
/**
 * 账户充值 v5.6 by 33hao.com
 *
 *
 *
 *
 * 
 */



defined('In33hao') or exit('Access Invalid!');

class member_payment_rechargeControl extends mobileMemberControl {

    private $payment_code;

    private $payment_config;


	public function __construct(){
		parent::__construct();
		
        if(!$_POST['payment_code']) {

            $payment_code = 'alipay';
			if (isset($_GET['payment_code'])) {

                $payment_code = $_GET['payment_code'];

            }
			


            $model_mb_payment = Model('mb_payment');

            $condition = array();

            $condition['payment_code'] = $payment_code;

            $mb_payment_info = $model_mb_payment->getMbPaymentOpenInfo($condition);

            if(!$mb_payment_info) {

				echo '支付方式未开启';
                output_error('支付方式未开启');

            }



            $this->payment_code = $payment_code;

            $this->payment_config = $mb_payment_info['payment_config'];



        }
	}


    /**
     * 账户充值
     */
    public function pd_payOp() {
        $pay_sn = $_GET['pay_sn'];
        $model_mb_payment = Model('mb_payment');
        $condition = array();
        $condition['payment_code'] = $this->payment_code;
        $mb_payment_info = $model_mb_payment->getMbPaymentOpenInfo($condition);
        if(!$mb_payment_info) {
            output_error('系统不支持选定的支付方式');
            exit();
        }
        $model_order= Model('predeposit');
        $pd_info = $model_order->getPdRechargeInfo(array('pdr_sn'=>$pay_sn,'pdr_member_id'=>$this->member_info['member_id']));
        if(empty($pd_info)){
            output_error('订单不存在!');
            exit();
        }
        if (intval($pd_info['pdr_payment_state'])) {
            output_error('您的订单已经支付，请勿重复支付!');
            exit();
        }

        $this->_api_pay_pd(array('pay_sn'=>$pd_info['pdr_sn'],'api_pay_amount'=>$pd_info['pdr_amount'],'order_type'=>'pd'), $mb_payment_info);

    }

    /**
     * 第三方在线支付接口
     *
     */
    private function _api_pay_pd($order_pay_info, $mb_payment_info) {
		
        $inc_file = BASE_PATH.DS.'api'.DS.'payment'.DS.$this->payment_code.DS.$this->payment_code.'.php';
        if(!is_file($inc_file)){
            output_error('支付接口不存在');
			echo '支付接口不存在';
			exit();
        }
        require($inc_file);
        $param = array();		
        $param = $mb_payment_info['payment_config'];
		
		// wxpay_jsapi

        if ($this->payment_code == 'wxpay_jsapi') {

            $param['orderSn'] = $order_pay_info['pay_sn'];

            $param['orderFee'] = (int) (100 * $order_pay_info['api_pay_amount']);

            $param['orderInfo'] = C('site_name') . '订单' . $order_pay_info['pay_sn'];

            $param['orderAttach'] = $order_pay_info['order_type'];

            $api = new wxpay_jsapi();

            $api->setConfigs($param);

            try {

                echo $api->paymentHtml($this);

            } catch (Exception $ex) {
               
            }

            exit;

        }
        $param['order_sn'] = $order_pay_info['pay_sn'];
        $param['order_amount'] = $order_pay_info['api_pay_amount'];
        $param['order_type'] = $order_pay_info['order_type'];
        $payment_api = new $this->payment_code();
        $return = $payment_api->submit($param);
        echo $return;
        exit;
    }
    
}
