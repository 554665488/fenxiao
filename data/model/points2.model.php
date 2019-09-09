<?php
/**
 * 积分及积分日志管理
 *
 *
 *




 */
defined('In33hao') or exit('Access Invalid!');

class points2Model extends Model {
    private $stage_arr;
    public function __construct(){
        parent::__construct();
        $this->stage_arr = array('inviter'=>'邀请注册');
    }
    /**
     * 操作积分
     * @author 33Hao Develop Team
     * @param  string $stage 操作阶段 regist(注册),login(登录),comments(评论),order(下单),system(系统),other(其他),pointorder(积分礼品兑换),app(同步积分兑换)
     * @param  array $insertarr 该数组可能包含信息 array('pl_memberid'=>'会员编号','pl_membername'=>'会员名称','pl_adminid'=>'管理员编号','pl_adminname'=>'管理员名称','pl_points'=>'积分','pl_desc'=>'描述','orderprice'=>'订单金额','order_sn'=>'订单编号','order_id'=>'订单序号','point_ordersn'=>'积分兑换订单编号');
     * @param  bool $if_repeat 是否可以重复记录的信息,true可以重复记录，false不可以重复记录，默认为true
     * @return bool
     */
    function savePointsLog($stage,$insertarr,$if_repeat = true){
        if (!$insertarr['pl_memberid']){
            return false;
        }
        //记录原因文字
        switch ($stage){
            case 'system':
                break;
            case 'pointorder':
                if (!$insertarr['pl_desc']){
                    $insertarr['pl_desc'] = '兑换礼品信息'.$insertarr['point_ordersn'].'消耗积分';
                }
                break;
            case 'inviter':
          				if (!$insertarr['pl_desc']){
          					$insertarr['pl_desc'] = '邀请新会员['.$insertarr['invited'].']注册';
          				}
          				$insertarr['pl_points'] = 20;
                  $model_setting = Model('setting');
                  $list_setting = $model_setting->getRewardSetting();
                  if(floatval($list_setting['invite_amount']) > 0) {
                    $insertarr['pl_points'] = floatval($list_setting['invite_amount']);
                  }
          				break;
	//积分抵用
            case 'pointstomoney':
                if (!$insertarr['pl_desc']){
                    $insertarr['pl_desc'] = "商品购买抵用优惠券，消费订单号:".$insertarr['order_sn'];
                }
                break;
	    case 'cashconvert':
                if (!$insertarr['pl_desc']){
                    $insertarr['pl_desc'] = "提现转换股权币";
                }
                break;
            case 'other':
                break;
        }
        $save_sign = true;
        if ($if_repeat == false){
            //检测是否有相关信息存在，如果没有，入库
            $condition['pl_memberid'] = $insertarr['pl_memberid'];
            $condition['pl_stage'] = $stage;
            $log_array = self::getPointsInfo($condition);
            if (!empty($log_array)){
                $save_sign = false;
            }
        }
        if ($save_sign == false){
            return true;
        }
        //新增日志
        $value_array = array();
        $value_array['pl_memberid'] = $insertarr['pl_memberid'];
        $value_array['pl_membername'] = $insertarr['pl_membername'];
        if ($insertarr['pl_adminid']){
            $value_array['pl_adminid'] = $insertarr['pl_adminid'];
        }
        if ($insertarr['pl_adminname']){
            $value_array['pl_adminname'] = $insertarr['pl_adminname'];
        }
        $value_array['pl_points'] = $insertarr['pl_points'];
        $value_array['pl_addtime'] = time();
        $value_array['pl_desc'] = $insertarr['pl_desc'];
        $value_array['pl_stage'] = $stage;
        $result = false;
        if($value_array['pl_points'] != '0'){
            $result = self::addPointsLog($value_array);
        }
        if ($result){
            //更新member内容
            $obj_member = Model('member');
            $upmember_array = array();
            $upmember_array['member_points2'] = array('exp','member_points2+'.$insertarr['pl_points']);
            $obj_member->editMember(array('member_id'=>$insertarr['pl_memberid']),$upmember_array);
            return true;
        }else {
            return false;
        }

    }
    /**
     * 添加积分日志信息
     *
     * @param array $param 添加信息数组
     */
    public function addPointsLog($param) {
        if(empty($param)) {
            return false;
        }
        return $this->table('points_log2')->insert($param);
    }
    /**
     * 积分日志列表
     *
     * @param array $condition 条件数组
     * @param array $page   分页
     * @param array $field   查询字段
     * @param array $page   分页
     */
    public function getPointsLogList($where, $field = '*', $limit = 0, $page = 0, $order = '', $group = ''){
        $order = $order ? $order : 'pl_id desc';
        $list = array();
        if (is_array($page)){
            if ($page[1] > 0){
                $list = $this->table('points_log2')->field($field)->where($where)->page($page[0],$page[1])->limit($limit)->order($order)->group($group)->select();
            } else {
                $list = $this->table('points_log2')->field($field)->where($where)->page($page[0])->limit($limit)->order($order)->group($group)->select();
            }
        } else {
            $list = $this->table('points_log2')->field($field)->where($where)->page($page)->limit($limit)->order($order)->group($group)->select();
        }
        if ($list && is_array($list)){
            foreach ($list as $k=>$v){
                $v['stagetext'] = $this->stage_arr[$v['pl_stage']];
                $v['addtimetext'] = @date('Y-m-d',$v['pl_addtime']);
                $list[$k] = $v;
            }
        }
        return $list;
    }
    /**
     * 积分日志详细信息
     *
     * @param array $condition 条件数组
     * @param array $field   查询字段
     */
    public function getPointsInfo($where = array(), $field = '*', $order = '',$group = ''){
        $info = $this->table('points_log2')->where($where)->field($field)->order($order)->group($group)->find();
        if (!$info){
            return array();
        }
        if($info['pl_stage']){
            $info['stagetext'] = $this->stage_arr[$info['pl_stage']];
        }
        if ($info['pl_addtime']) {
            $info['addtimetext'] = @date('Y-m-d',$info['pl_addtime']);
        }
        return $info;
    }
     /**
     * 判断当天是否领取了红包
     *
     * @param array $condition 条件数组
     * @param array $field   查询字段array('neq', $member_id);
     */
    public function gethongbaohas($member_id){
        $where = array();
        $where['pl_memberid'] =$member_id;
        $where['pl_stage'] = 'hongbao'; //已卖
        //$where['FROM_UNIXTIME(pl_addtime, %Y-%m-%d)'] =array('eq',@date('Y-m-d',time()));
        $where['pl_addtime'] =array('gt',strtotime(date("Y-m-d")));
        return $this->table('points_log2')->where($where)->count();

    }

    /**
     * 红包领取日志
     */
    public function gethongbaorecord($template_info, $member_id, $member_name = ''){
        if (intval($member_id) <= 0 || empty($template_info)){
            return array('state'=>false,'msg'=>'参数错误');
        }
        //查询会员信息
        if (!$member_name){
            $member_info = Model('member')->getMemberInfoByID($member_id);
            if (empty($template_info)){
                return array('state'=>false,'msg'=>'参数错误');
            }
            $member_name = $member_info['member_name'];
        }

        //红包日志member_points2
        $points_arr['pl_memberid'] = $member_id;
        $points_arr['pl_membername'] = $member_name;
        $points_arr['pl_points'] = $template_info['hongbao_num'];
        $points_arr['pl_addtime'] = time();
        $points_arr['pl_desc'] = "每天领取红包";
        $points_arr['pl_stage'] = "hongbao";
        $result = Model('points2')->savePointsLog('hongbao',$points_arr,true);
        if (!$result){
            return array('state'=>false,'msg'=>'红包领取日志失败');
        }else{
            return array('state'=>true,'msg'=>'操作成功');
        }
    }
}
