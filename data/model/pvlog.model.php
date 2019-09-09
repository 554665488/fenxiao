<?php
/**
 * PV日志管理
 *
 *
 *
 */
defined('In33hao') or exit('Access Invalid!');

class pvlogModel extends Model {
    private $stage_arr;
    public function __construct(){
        parent::__construct();
        $this->stage_arr = array('system'=>'系统操作', 'system_og' => '原始仓奖励', 'transfer_to' => '转送', 'transfer_recv' => '接收' );
    }
    /**
     * 操作积分
     * @author 33Hao Develop Team
     * @param  string $stage 操作阶段 regist(注册),login(登录),comments(评论),order(下单),system(系统),other(其他),pointorder(积分礼品兑换),app(同步积分兑换)
     * @param  array $insertarr 该数组可能包含信息 array('pl_memberid'=>'会员编号','pl_membername'=>'会员名称','pl_adminid'=>'管理员编号','pl_adminname'=>'管理员名称','pl_points'=>'积分','pl_desc'=>'描述','orderprice'=>'订单金额','order_sn'=>'订单编号','order_id'=>'订单序号','point_ordersn'=>'积分兑换订单编号');
     * @param  bool $if_repeat 是否可以重复记录的信息,true可以重复记录，false不可以重复记录，默认为true
     * @return bool
     */
    function savePointsLog($stage,$insertarr){
        if (!$insertarr['pl_memberid']){
            return false;
        }
        //记录原因文字
        switch ($stage){
            case 'system':
                break;
           
	    case 'transfer_to':
                break;
		case 'transfer_recv':
                break;
            case 'other':
                break;
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
            $upmember_array['guquan'] = array('exp','guquan+'.$insertarr['pl_points']);
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
        return $this->table('pv_log')->insert($param);
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
                $list = $this->table('pv_log')->field($field)->where($where)->page($page[0],$page[1])->limit($limit)->order($order)->group($group)->select();
            } else {
                $list = $this->table('pv_log')->field($field)->where($where)->page($page[0])->limit($limit)->order($order)->group($group)->select();
            }
        } else {
            $list = $this->table('pv_log')->field($field)->where($where)->page($page)->limit($limit)->order($order)->group($group)->select();
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
        $info = $this->table('pv_log')->where($where)->field($field)->order($order)->group($group)->find();
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
    
}
