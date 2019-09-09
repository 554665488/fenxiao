<?php
/**
 * cms首页
 */



defined('In33hao') or exit('Access Invalid!');
class indexControl extends CMSHomeControl{

    public function __construct() {
        parent::__construct();
        Tpl::output('index_sign','index');
    }
    public function indexOp(){
        //Tpl::showpage('index');
		header('location: index.php?act=member_article&op=article_list');
    }
}
