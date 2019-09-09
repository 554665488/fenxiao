var page = pagesize;
var curpage = 1;
var hasmore = true;
var footer = false;
var class_id = getQueryString("class_id");

$(function() {


	get_list();
	$(window).scroll(function() {
		if ($(window).scrollTop() + $(window).height() > $(document).height() - 1) {
			get_list()
		}
	});
	class_list()
});

function get_list() {
	$(".loading").remove();
	if (!hasmore) {
		return false
	}
	hasmore = false;
	param = {};
	param.page = page;
	param.curpage = curpage;
	if (class_id != "") {
		param.class_id = class_id
	}

	$.getJSON(ApiUrl + "/index.php?act=cms_article&op=article_list" + window.location.search.replace("?", "&"), param, function(e) {
		if (!e) {
			e = [];
			e.datas = [];
			e.datas.article_list = []
		}
		$(".loading").remove();
		curpage++;
		var r = template.render("home_body", e);
		$("#cart-list-wp .news-list").append(r);
		hasmore = e.hasmore
	})
}
function class_list() {
	param = {};
	if (class_id != "") {
		param.class_id = class_id
	}
	$.getJSON(ApiUrl + "/index.php?act=cms_article&op=class_list", param, function(e) {
		var r = e.datas;
		$("#loadClass").html(template.render("class_list", r));
		$('title').html(e.datas.class_name);
       //$(".news-menu-list ul").css("width",$(".news-menu-list ul li").innerWidth() *$(".news-menu-list ul li").length);
	})
}
function init_get_list(e, r) {
	order = e;
	key = r;
	curpage = 1;
	hasmore = true;
	$("#cart-list-wp .news-list").html("");
	$("#footer").removeClass("posa");
	get_list()
}