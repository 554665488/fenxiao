<?php
/**
 * 文章 cms
 * @好商城v5 (c) 2005-2016 33hao Inc.
 * @license    http://www.33hao.com
 * @link       交流群号：216611541
 * @since      好商城提供技术支持 授权请购买shopnc授权
 * 
 **/

defined('In33hao') or exit('Access Invalid!');
class cms_articleControl extends mobileHomeControl{

	public function __construct() {
        parent::__construct();
    }
	
	public function indexOp() {
        $this->article_listOp();
    }

    /**
     * cms文章列表
     */
    public function article_listOp() {	
		 $condition = array();
		 $condition['article_state']=3;
        if(!empty($_GET['class_id'])) {
            $condition['article_class_id'] = intval($_GET['class_id']);
        }
        $model_article = Model('cms_article');
        $article_list = $model_article->getListWithClassName($condition, 10, 'article_sort asc, article_id desc');
		$page_count = $model_article->gettotalpage();
        
		
	 	if(!empty($article_list) && is_array($article_list))
		{
			foreach($article_list as $k=>$value)
			{
				$value['img']='';
				if(!empty($value['article_image'])){
					$value['img']= getCMSArticleImageUrl($value['article_attachment_path'], $value['article_image'], 'list');
				}
				$value['time'] = date('Y-m-d',$value['article_publish_time']);
				$article_list[$k]=$value;
			}
		} 
       
		output_data(array('article_list' => $article_list), mobile_page($page_count));
    }
    /**
     * cms类型
     */
	public function class_listOp(){
		$nowid=0;
		if(isset($_GET['class_id']))$nowid=intval($_GET['class_id']);
		 $art_class=Model('cms_article_class')->getList(array());       
		output_data(array('article_class_list'=>$art_class,'nowid'=>$nowid));
	}
	 /**
     * cms文章详情
     */
	public function cms_showOp(){
		$article_id = intval($_GET['article_id']);
        if($article_id <= 0) {
            output_error('文章不存在');
        }
		$model_article = Model('cms_article');
		$article_detail = $model_article->getOne(array('article_id'=>$article_id));
		$article_detail['article_time'] = date('Y-m-d',$article_detail['article_publish_time']);
		if(empty($article_detail)) {
            output_error('文章不存在');
        }
		//计数加1
        $model_article->modify(array('article_click'=>array('exp','article_click+1')),array('article_id'=>$article_id));
		output_data($article_detail);
	}
	/**
     * 评论列表
     **/
    public function comment_listOp() {
        $page_count = 10;
        $order = 'comment_id desc';
        $comment_object_id = intval($_GET['comment_object_id']);
        $comment_type = 0;
        switch ($_GET['type']) {
        case 'article':
            $comment_type = 1;
            break;
        case 'picture':
            $comment_type = 2;
            break;
        }

        if($comment_object_id > 0 && $comment_type > 0) {
            $condition = array();
            $condition["comment_object_id"] = $comment_object_id;
            $condition["comment_type"] = $comment_type;
            $model_cms_comment = Model('cms_comment');
            $comment_list = $model_cms_comment->getListWithUserInfo($condition, $page_count, $order);
			$all_count = $model_cms_comment->gettotalpage();
			if(!empty($comment_list) && is_array($comment_list))
			{
				foreach($comment_list as $k=>$val)
				{
					$val['member_avatar'] = getMemberAvatar($val['member_avatar']);
					$comment_list[$k] = $val;
				}
			}
			output_data(array('comment_list'=>$comment_list), mobile_page($all_count));
        }
    }
	
	/**
     * 文章点赞顶
     **/
    public function article_upOp() {

        $data = array();
        $data['result'] = 'true';

        $article_id = intval($_POST['article_id']);
		
        if($article_id > 0) {
			$memberId=$this->getMemberIdIfExists();
			$model_attitude = Model('cms_article_attitude');
            $param = array();
            $param['attitude_article_id'] = $article_id;
            $param['attitude_member_id'] = $memberId;
            $exist = $model_attitude->isExist($param);
            if(!$exist) {
                $param['attitude_time'] = time();
                $result = $model_attitude->save($param);
                if($result) {

                    //点赞计数加1
                   $model_comment = Model('cms_article');
					$model_comment-> modify(array('article_attitude_5'=>array('exp', 'article_attitude_5+1')), array('article_id'=>$article_id));
					$data['status'] = '1';
					$data['message'] = '谢谢您点赞~';                   

                } else {
                    $data['result'] = 'false';
                    $data['message'] = '点赞失败';
                }
            } else {
                $data['result'] = 'false';
                $data['message'] = '您已经点赞过了哦~';
            }
           
        } else {
            $data['result'] = 'false';
            $data['message'] = '点赞失败';
        }
		output_data($data);
    }
	
	/**
     * 评论顶
     **/
    public function comment_upOp() {

        $data = array();
        $data['result'] = 'true';

        $comment_id = intval($_POST['comment_id']);
        if($comment_id > 0) {
			$memberId=$this->getMemberIdIfExists();
            $model_comment_up = Model('cms_comment_up');
            $param = array();
            $param['comment_id'] = $comment_id;
            $param['up_member_id'] = $memberId;
            $is_exist = $model_comment_up->isExist($param);
            if(!$is_exist) {
                $param['up_time'] = time();
                $model_comment_up->save($param);

                $model_comment = Model('cms_comment');
                $model_comment->modify(array('comment_up'=>array('exp', 'comment_up+1')), array('comment_id'=>$comment_id));
				$data['status'] = '1';
                $data['message'] = '谢谢您！';
            } else {
                $data['result'] = 'false';
                $data['message'] = '顶过了';
            }
        } else {
            $data['result'] = 'false';
            $data['message'] = Language::get('wrong_argument');
        }
		output_data($data);
    }

    /**
     * 评论保存
     **/
    public function comment_saveOp() {

        $data = array();
        $data['result'] = 'true';
        $comment_object_id = intval($_POST['comment_object_id']);
        $comment_type = $_POST['comment_type'];
        $model_name = '';
        $count_field = '';
        switch ($comment_type) {
        case 'article':
            $comment_type = 1;
            $model_name = 'cms_article';
            $count_field = 'article_comment_count';
            $comment_object_key = 'article_id';
            break;
        case 'picture':
            $comment_type = 2;
            $model_name = 'cms_picture';
            $count_field = 'picture_comment_count';
            $comment_object_key = 'picture_id';
            break;
        default:
            $comment_type = 0;
            break;
        }
		 

        if($comment_object_id <= 0 || empty($comment_type) || empty($_POST['comment_message'])) {
            $data['result'] = 'false';
            $data['message'] = Language::get('wrong_argument');
			output_data($data);
        }
		
		$memberId=$this->getMemberIdIfExists();

        if($memberId>0) {

            $param = array();
            $param['comment_type'] = $comment_type;
            $param["comment_object_id"] = $comment_object_id;
            if (strtoupper(CHARSET) == 'GBK'){
                $param['comment_message'] = Language::getGBK(trim($_POST['comment_message']));
            } else {
                $param['comment_message'] = trim($_POST['comment_message']);
            }
            $param['comment_member_id'] = $memberId;
            $param['comment_time'] = time();

            $model_comment = Model('cms_comment');

            if(!empty($_POST['comment_id'])) {
                $comment_detail = $model_comment->getOne(array('comment_id'=>$_POST['comment_id']));
                if(empty($comment_detail['comment_quote'])) {
                    $param['comment_quote'] = $_POST['comment_id'];
                } else {
                    $param['comment_quote'] = $comment_detail['comment_quote'].','.$_POST['comment_id'];
                }
            } else {
                $param['comment_quote'] = '';
            }

            $result = $model_comment->save($param);
            if($result) {

                //评论计数加1
                $model = Model($model_name);
                $update = array();
                $update[$count_field] = array('exp',$count_field.'+1');
                $condition = array();
                $condition[$comment_object_key] = $comment_object_id;
                $model->modify($update, $condition);
				$data['status'] = '1';
				$data['message'] = '发表成功！';

            } else {
                $data['result'] = 'false';
                $data['message'] = '发表失败！';
            }
        } else {
            $data['result'] = 'false';
            $data['message'] = '请登录！';
        }
		output_data($data);
    }
	

  
}
