<!doctype html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE9" />
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET;?>">
<title><?php echo empty($output['seo_title'])?$output['html_title']:$output['seo_title'].'-'.$output['html_title'];?></title>
<meta name="keywords" content="<?php echo $output['seo_keywords']; ?>" />
<meta name="description" content="<?php echo $output['seo_description']; ?>" />
<link href="<?php echo CMS_TEMPLATES_URL;?>/css/base.css" rel="stylesheet" type="text/css">
<link href="<?php echo CMS_TEMPLATES_URL;?>/css/layout.css" rel="stylesheet" type="text/css">
<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
      <script src="<?php echo RESOURCE_SITE_URL;?>/js/html5shiv.js"></script>
      <script src="<?php echo RESOURCE_SITE_URL;?>/js/respond.min.js"></script>
<![endif]-->
<script>
var COOKIE_PRE = '<?php echo COOKIE_PRE;?>'; var _CHARSET = '<?php echo strtolower(CHARSET);?>'; var LOGIN_SITE_URL = '<?php echo LOGIN_SITE_URL;?>';var MEMBER_SITE_URL = '<?php echo MEMBER_SITE_URL;?>'; var SITEURL = '<?php echo SHOP_SITE_URL;?>'; var SHOP_SITE_URL = '<?php echo SHOP_SITE_URL;?>'; var RESOURCE_SITE_URL = '<?php echo RESOURCE_SITE_URL;?>';
</script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script>
<script id="dialog_js" type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/dialog/dialog.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo CMS_RESOURCE_SITE_URL;?>/js/common.js" charset="utf-8"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/perfect-scrollbar.min.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/perfect-scrollbar.min.js"></script>
<script type="text/javascript">
var PRICE_FORMAT = '<?php echo $lang['currency'];?>%s';
var LOADING_IMAGE = '<?php echo getLoadingImage();?>';
$(function(){
	//search
	$("#searchCMS").children('ul').children('li').click(function(){
		$(this).parent().children('li').removeClass("current");
		$(this).addClass("current");
        $("#form_search").attr("action", $(this).attr("action"));
        $("#act").val($(this).attr("act"));
        $("#op").val($(this).attr("op"));
	});
    var search_current_item = $("#searchCMS").children('ul').children('li.current');
    $("#form_search").attr("action", search_current_item.attr("action"));
    $("#act").val(search_current_item.attr("act"));
    $("#op").val(search_current_item.attr("op"));
});
//登录开关状态
var connect_qq = "<?php echo C('qq_isuse');?>";
var connect_sn = "<?php echo C('sina_isuse');?>";
var connect_wx = "<?php echo C('weixin_isuse');?>";

var connect_weixin_appid = "<?php echo C('weixin_appid');?>";
</script>
</head>
<body>
<!-- 头 -->
<div id="append_parent"></div>
<div id="ajaxwaitid"></div>
<div class="public-top-layout w">
  <div class="topbar warp-all">
    <div class="user-entry">
      <?php if($_SESSION['is_login'] == '1'){?>
      <?php echo $lang['nc_hello'];?><span><a href="<?php echo urlShop('member', 'home');?>"><?php echo str_cut($_SESSION['member_name'],20);?></a></span><?php echo $lang['nc_comma'],$lang['welcome_to_site'];?> <a href="<?php echo SHOP_SITE_URL;?>"  title="<?php echo $lang['homepage'];?>" alt="<?php echo $lang['homepage'];?>"><span><?php echo $output['setting_config']['site_name']; ?></span></a> <span></span>
      <?php }else{?>
      <?php echo $lang['nc_hello'].$lang['nc_comma'].$lang['welcome_to_site'];?> <a href="<?php echo SHOP_SITE_URL;?>" title="<?php echo $lang['homepage'];?>" alt="<?php echo $lang['homepage'];?>"><?php echo $output['setting_config']['site_name']; ?></a> <span>[<a href="<?php echo urlLogin('login');?>"><?php echo $lang['nc_login'];?></a>]</span> <span></span>
      <?php }?>
    </div>
    <div class="quick-menu">
      <?php
      if(!empty($output['nav_list']) && is_array($output['nav_list'])){
	      foreach($output['nav_list'] as $nav){
	      if($nav['nav_location']<1){
	      	$output['nav_list_top'][] = $nav;
	      }
	      }
      }
      if(!empty($output['nav_list_top']) && is_array($output['nav_list_top'])){
      	?>
      <dl>
        <dt>站点导航<i></i></dt>
        <dd>
          <ul>
            <?php foreach($output['nav_list_top'] as $nav){?>
            <li><a
        <?php
        if($nav['nav_new_open']) {
            echo ' target="_blank"';
        }
        echo ' href="';
        switch($nav['nav_type']) {
        	case '0':echo $nav['nav_url'];break;
    	case '1':echo urlShop('search', 'index', array('cate_id'=>$nav['item_id']));break;
    	case '2':echo urlMember('article', 'article',array('ac_id'=>$nav['item_id']));break;
    	case '3':echo urlShop('activity', 'index',array('activity_id'=>$nav['item_id']));break;
        }
        echo '"';
        ?>><?php echo $nav['nav_title'];?></a></li>
            <?php }?>
          </ul>
        </dd>
      </dl>
      <?php }?>
    </div>
  </div>
</div>
<script type="text/javascript">
$(function(){
	$(".quick-menu dl").hover(function() {
		$(this).addClass("hover");
	},
	function() {
		$(this).removeClass("hover");
	});

});
</script>
<header id="topHeader">
  <div class="warp-all">
    <div class="cms-logo"> <a href="<?php echo CMS_SITE_URL;?>">
      <?php if(empty($output['setting_config']['cms_logo'])) { ?>
      <img src="<?php echo UPLOAD_SITE_URL.DS.ATTACH_CMS.DS.'cms_default_logo.png';?>">
      <?php } else { ?>
      <img src="<?php echo UPLOAD_SITE_URL.DS.ATTACH_CMS.DS.$output['setting_config']['cms_logo'];?>">
      <?php } ?>
      </a> </div>
    <div class="search-cms" id="searchCMS">
      <ul class="tab">
        <li <?php if($_GET['act'] != 'picture' ) echo 'class="current"'; ?> action="<?php echo CMS_SITE_URL.DS;?>index.php" act="article" op="article_search"><?php echo $lang['cms_article'];?><i></i></li>
        <li <?php if($_GET['act'] == 'picture' ) echo 'class="current"'; ?> action="<?php echo CMS_SITE_URL.DS;?>index.php" act="picture" op="picture_search"><?php echo $lang['cms_picture'];?><i></i></li>
        <li action="<?php echo SHOP_SITE_URL.DS;?>index.php" act="search"><?php echo $lang['cms_goods'];?><i></i></li>
      </ul>
      <div class="form-box">
        <form id="form_search" method="get" action="" >
          <input id="act" name="act" type="hidden" />
          <input id="op" name="op" type="hidden" />
          <input id="keyword" name="keyword" type="text" class="input-text" value="<?php echo isset($_GET['keyword'])?$_GET['keyword']:'';?>" maxlength="60" x-webkit-speech="" lang="zh-CN" onwebkitspeechchange="foo()" x-webkit-grammar="builtin:search" />
          <input id="btn_search" type="submit" class="input-btn" value="<?php echo $lang['cms_text_search'];?>">
        </form>
      </div>
    </div>
    <?php if($output['top_function_block']) { ?>
    <!--演示用天气插件-->
    <div class="weather-box">
      <div class="content">
        <!--
        <iframe allowtransparency="true" frameborder="0" width="140" height="109" scrolling="no" src="http://tianqi.2345.com/plugin/widget/index.htm?s=1&v=1&f=1&b=&k=&t=1&a=1&c=54527&d=1&e=0"></iframe>
-->
      </div>
    </div>
    <!--演示用天气插件 End-->
    <?php } ?>
  </div>
</header>
