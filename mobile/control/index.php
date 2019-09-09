<?php
/**
 * 手机端首页控制 33 h ao .com
 *
 * @好商城 (c) 2015-2018 33HAO Inc. (http://www.33hao.com)
 * @license    http://www.33 hao.c om
 * @link       交流群号：138182377
 * @since      好商城提供技术支持 授权请购买shopnc授权
 *
 */



defined('In33hao') or exit('Access Invalid!');
class indexControl extends mobileHomeControl{

    public function __construct() {
        parent::__construct();
    }
   /**
     * 领取红包
     */
    public function hongbaoOp() {
        $key = $this->getMemberIdIfExists();
        $model_points2 = Model('points2');
         $count=$model_points2->gethongbaohas($key);
         output_data($count);
         if($count>0){
            output_error('红包已领取');
         }
         else{
            output_data('0');

         }
    }

    /**
     * 红包领取日志
     */
    public function hongbaorecordOp(){

        $key = $this->getMemberIdIfExists();
        $model_setting = Model('setting');
        $list_setting = $model_setting->getRewardSetting();
        $hongbao_num = 5;
        if(floatval($list_setting['hongbao_amount']) > 0) {
          $hongbao_num = floatval($list_setting['hongbao_amount']);
        }
		
        $model_points2 = Model('points2');
		$count=$model_points2->gethongbaohas($key);
		if($count>0){
            output_error('红包已领取');
         }
        $where = array();
        $where['hongbao_num'] =$hongbao_num;
        try {
            $model_points2->beginTransaction();
             //更新个人账户A金券数量
              //新增日志记录
              //生成A金券订单
            $data = $model_points2->gethongbaorecord($where,  $key, '');
            if ($data['state'] == false) {
                throw new Exception($data['msg']);
            }
            $model_points2->commit();
            output_data('1');
        } catch (Exception $e) {
            $model_points2->rollback();
            output_error($e->getMessage());
        }

    }
    /**
     * 首页
     */
    public function indexOp() {

        $model_mb_special = Model('mb_special');
        $data = $model_mb_special->getMbSpecialIndex();
        $this->_output_special($data, $_GET['type']);
    }

    /**
     * 专题
     */
    public function specialOp() {
        $model_mb_special = Model('mb_special');
        $info = $model_mb_special->getMbSpecialInfoByID($_GET['special_id']);
        $list = $model_mb_special->getMbSpecialItemUsableListByID($_GET['special_id']);
        $data = array_merge($info, array('list' => $list));
        $this->_output_special($data, $_GET['type'], $_GET['special_id']);
    }

    /**
     * 输出专题
     */
    private function _output_special($data, $type = 'json', $special_id = 0) {
        $model_special = Model('mb_special');
        if($_GET['type'] == 'html') {
            $html_path = $model_special->getMbSpecialHtmlPath($special_id);
            if(!is_file($html_path)) {
                ob_start();
                Tpl::output('list', $data);
                Tpl::showpage('mb_special');
                file_put_contents($html_path, ob_get_clean());
            }
            header('Location: ' . $model_special->getMbSpecialHtmlUrl($special_id));
            die;
        } else {
            output_data($data);
        }
    }

    /**
     * android客户端版本号
     */
    public function apk_versionOp() {
        $version = C('mobile_apk_version');
        $url = C('mobile_apk');
        if(empty($version)) {
           $version = '';
        }
        if(empty($url)) {
            $url = '';
        }

        output_data(array('version' => $version, 'url' => $url));
    }

    /**
     * 默认搜索词列表
     */
    public function search_key_listOp() {
        $list = @explode(',',C('hot_search'));
        if (!$list || !is_array($list)) {
            $list = array();
        }
        if ($_COOKIE['hisSearch'] != '') {
            $his_search_list = explode('~', $_COOKIE['hisSearch']);
        }
        if (!$his_search_list || !is_array($his_search_list)) {
            $his_search_list = array();
        }
        output_data(array('list'=>$list,'his_list'=>$his_search_list));
    }

    /**
     * 热门搜索列表
     */
    public function search_hot_infoOp() {
        if (C('rec_search') != '') {
            $rec_search_list = @unserialize(C('rec_search'));
        }
        $rec_search_list = is_array($rec_search_list) ? $rec_search_list : array();
        $result = $rec_search_list[array_rand($rec_search_list)];
        output_data(array('hot_info'=>$result ? $result : array()));
    }

    /**
     * 高级搜索
     */
    public function search_advOp() {
        $area_list = Model('area')->getAreaList(array('area_deep'=>1),'area_id,area_name');
        if (C('contract_allow') == 1) {
            $contract_list = Model('contract')->getContractItemByCache();
            $_tmp = array();$i = 0;
            foreach ($contract_list as $k => $v) {
                $_tmp[$i]['id'] = $v['cti_id'];
                $_tmp[$i]['name'] = $v['cti_name'];
                $i++;
            }
        }
        output_data(array('area_list'=>$area_list ? $area_list : array(),'contract_list'=>$_tmp));
    }

	/**
     * 公告列表 33hao 5.2
     */
    public function getggOp() {
        if(!empty($_GET['ac_id']) && intval($_GET['ac_id']) > 0)
		{
			$article_class_model	= Model('article_class');
			$article_model	= Model('article');
			$condition	= array();

			$child_class_list = $article_class_model->getChildClass(intval($_GET['ac_id']));
			$ac_ids	= array();
			if(!empty($child_class_list) && is_array($child_class_list)){
				foreach ($child_class_list as $v){
					$ac_ids[]	= $v['ac_id'];
				}
			}
			$ac_ids	= implode(',',$ac_ids);
			$condition['ac_ids']	= $ac_ids;
			$condition['article_show']	= '1';
			$article_list = $article_model->getArticleList($condition,5);
			//$article_type_name = $this->article_type_name($ac_ids);
			//output_data(array('article_list' => $article_list, 'article_type_name'=> $article_type_name));
			output_data(array('article_list' => $article_list));
		}
		else {
			output_error('缺少参数:文章类别编号');
		}
    }

    public function taskOp() {
      if($_GET['tt'] !='e10adc3949ba59abbe56e057f20f883e') {
        return;
      }
      $model_member = Model('member');
      $members = $model_member->table('member')->field("*")->limit(5000)->select();
      $model_points = Model('points');
			foreach($members as $member) {
				if(floatval($member['points_freeze']) <= 0) {
					continue;
				}
        $release = floatval($member['points_freeze']) * 0.01;
        $release = round($release, 2);
        //用户释放仓
        $insert_arr = array();
        $insert_arr['pl_memberid'] = $member['member_id'];
        $insert_arr['pl_membername'] = $member['member_name'];
        $insert_arr['pl_points'] = -$release;
        $insert_arr['pl_desc'] = '会员['.$member['member_name'].']冻结仓转换释放仓'.ncPriceFormat($release).',日期'.date('Y-m-d');
        $result = $model_points->savePointsOtherLog('freeze_to_release',$insert_arr,false);

			}
      echo "OK";
    }

}
