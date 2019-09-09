var page = pagesize;
var curpage = 1;
var hasmore = true;
var footer = false;
var article_id = getQueryString('article_id');
$(function(){
	
	loadPLlist();
	$(window).scroll(function() {
		if ($(window).scrollTop() + $(window).height() > $(document).height() - 1) {
			loadPLlist()
		}
	});
	
	
	
	if (article_id=='') {
    	window.location.href = WapSiteUrl + '/index.html';
    	return;
	}
	else {
		$.ajax({
			url:ApiUrl+"/index.php?act=cms_article&op=cms_show",
			type:'get',
			data:{article_id:article_id},
			jsonp:'callback',
			dataType:'jsonp',
			success:function(result){
				$('title').html(result.datas.article_title);
				var data = result.datas;
				var html = template.render('article', data);				
				$(".newsinfo").html(html);
				$(".pl em").html(data.article_comment_count);
				$(".zan em").html(data.article_attitude_5);
				$(".word").html(data.article_content);
			}
		});
	}	
});

function loadPLlist() {
	$(".loading").remove();
	if (!hasmore) {
		return false
	}
	hasmore = false;
	param = {};
	param.page = page;
	param.curpage = curpage;
  param.comment_object_id = article_id;
  param.type = 'article';

	$.getJSON(ApiUrl+"/index.php?act=cms_article&op=comment_list" + window.location.search.replace("?", "&"), param, function(e) {
	/*	if (!e) {
			e = [];
			e.datas = [];
			e.datas.comment_list = []
		}*/
		
		$(".loading").remove();
		curpage++;
		var r = template.render("comment_list", e);
		$("#comment_list_all").append(r);

		
		hasmore = e.hasmore;
	});
}

function init_get_list(e, r) {
	order = e;
	key = r;
	curpage = 1;
	hasmore = true;
	$("#comment_list_all").html("");
	$("#footer").removeClass("posa");
	loadPLlist()
}

/*
 * 对文章进行点赞处理
 */
function zan(){
	var key = getCookie('key');
	 if (key === null) {
        window.location.href = WapSiteUrl + '/tmpl/member/login.html';
        return;
    }
		if (!conFlg) {
			conFlg = true;
			 $.ajax({
            url:ApiUrl+"/index.php?act=cms_article&op=article_up",
            type:"post",
            dataType:"json",
            data:{key:key, article_id:article_id},
            success:function (res){
				conFlg = false;
				if (res.datas.status == 1) {
					layer.open({
						content: res.datas.message,
						shadeClose: false,
						btn: ['确定'],
						end: function(){
							window.location.reload()
						}
					});
				} else{
					layer.open({
						content: res.datas.message,
						shadeClose: false,
						btn: ['好的']
					});
				}
			}
		});
	}

}

function _itemz(id,his){
	var key = getCookie('key');
	 if (key === null) {
        window.location.href = WapSiteUrl + '/tmpl/member/login.html';
        return;
   }
	var zzan=$("#zanc_"+id);
 	var count = parseInt(zzan.html());
	var keys = 'pinlunzan' + id;
	if (getCookie(keys) == 1) {
		return false;
	} else {
					 $.ajax({
            url:ApiUrl+"/index.php?act=cms_article&op=comment_up",
            type:"post",
            dataType:"json",
            data:{key:key, comment_id:id},
            success:function (res){
				if (res.datas.status == 1) {
					layer.open({
					content: res.datas.message,
					shadeClose: false,
					btn: ['确定'],
					end: function(){
						addCookie(keys, 1);
						zzan.className='iconfont icon-icon item-zan itemzan curZan'; 
						zzan.html(count + 1);
					}
					});
				} else{
					layer.open({
						content: res.datas.message,
						shadeClose: false,
						btn: ['好的']
					});
				}
			}
		});
	}
}
