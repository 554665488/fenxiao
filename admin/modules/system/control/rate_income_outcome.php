<?php
/**
 * 系统拨出比
 *
 *
 *
*/
defined('In33hao') or exit('Access Invalid!');
class rate_income_outcomeControl extends SystemControl{
    
    public function __construct(){
        parent::__construct();
    }

    public function indexOp() {
		$model_order = Model('order');
		$model_member = Model('member');
		$model_pd = Model('predeposit');

		$startTime = strtotime($_POST['startTime']);
		$endTime = strtotime($_POST['endTime']);
		if(!$startTime){
			$startTime = strtotime(date('Y-m-d',time()));
		}
		if(!$endTime){
			$endTime = time() + 24 * 60 * 60;
		}
		//新增会员
		$member_count = $model_member->getMemberCount(array('member_time'=>array('BETWEEN',$startTime.','.$endTime),'vip'=>1));
		$member_count2 = $model_member->getMemberCount(array('member_time'=>array('BETWEEN',$startTime.','.$endTime)));
		$amount = $model_order->table('orders')->where(array('order_state' => array('egt',20), 'circle_num' => 1, 'is_wholesale' => 0, 'payment_time' => array('BETWEEN',$startTime.','.$endTime)))->sum('order_amount');
		$amount2 = $model_order->table('orders')->where(array('order_state' => array('egt',20), 'circle_num' => array('gt',1), 'is_wholesale' => 0, 'payment_time' => array('BETWEEN',$startTime.','.$endTime)))->sum('order_amount');
		Tpl::output('orderamount',$amount);
		Tpl::output('orderamount2',$amount2);
		Tpl::output('startTime',$startTime);
		Tpl::output('endTime',$endTime);
		Tpl::output('member_count',$member_count);
		Tpl::output('member_count2',$member_count2);
		
		$amount = $model_pd->getPdAmount(array('lg_add_time'=>array('BETWEEN',$startTime.','.$endTime),'lg_type'=>'sys_add_money'));
		Tpl::output('amount',$amount);
		
		$amount2 = $model_pd->getPdAmount(array('lg_add_time'=>array('BETWEEN',$startTime.','.$endTime),'lg_type'=>'sys_del_money'));
		Tpl::output('amount2',$amount2);
		$amount3 = $amount + $amount2;
		Tpl::output('amount3',$amount3);
		$pd_amount = $model_pd->getPdAmount(array('lg_add_time'=>array('BETWEEN',$startTime.','.$endTime),'lg_type'=>'sys_add_money_reward'));
		Tpl::output('pd_amount',$pd_amount);
		
		//$cash_amount = $model_pd->getPdCashAmount(array('pdc_add_time'=>array('BETWEEN',$startTime.','.$endTime),'pdc_payment_state'=>1));
		//Tpl::output('cash_amount',$cash_amount);

		Tpl::setDirquna('system');
        Tpl::showpage('rate_income_outcome');
    }


}
