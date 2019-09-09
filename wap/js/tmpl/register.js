$(function(){

	var uid = window.location.href.split("#V5");
	var  fragment = uid[1];
	if(fragment){
		if (fragment.indexOf("V5") == 0) {
				addCookie("uid", "0");
			}else {
				addCookie("uid", fragment);
		}
	}


    var key = getCookie('key');
    if (key) {
        window.location.href = WapSiteUrl+'/tmpl/member/member.html';
        return;
    }
    $.getJSON(ApiUrl + '/index.php?act=connect&op=get_state&t=connect_sms_reg', function(result){
        if (result.datas != '0') {
            $('.register-tab').show();
        }
    });

	$.sValid.init({//注册验证
        rules:{
        	username:"required",
            phone:"required"
        },
        messages:{
            username:"用户名必须填写！",
            phone:"手机必填!"
        },
        callback:function (eId,eMsg,eRules){
            if(eId.length >0){
                var errorHtml = "";
                $.map(eMsg,function (idx,item){
                    errorHtml += "<p>"+idx+"</p>";
                });
                errorTipsShow(errorHtml);
            }else{
                errorTipsHide();
            }
        }
    });
	var register_member = 0;
	$('#registerbtn').click(function(){
        if (!$(this).parent().hasClass('ok')) {
            return false;
        }
		if(!$('#checkbox').prop('checked')) {
			errorTipsShow("请同意用户注册协议和玖久国际商城代售协议书");
            return false;
		}
		var username = $("input[name=username]").val();
		var pwd = $("input[name=pwd]").val();
		var password_confirm = $("input[name=password_confirm]").val();
		var paypwd = $("input[name=paypwd]").val();
		var paypassword_confirm = $("input[name=paypassword_confirm]").val();
		var phone = $("input[name=phone]").val();
		var client = 'wap';
		if (register_member) {
		    errorTipsShow("<p>正在处理中，请勿重复点击！</p>");
            return false;
        }
		if($.sValid()){
		    register_member = 1;
			$.ajax({
				type:'post',
				url:ApiUrl+"/index.php?act=login&op=register",
				data:{username:username,password:pwd,password_confirm:password_confirm,paypassword:paypwd,paypassword_confirm:paypassword_confirm,phone:phone,
					client:client, recmember: $('#recmember').val(),nodemember: $('#nodemember').val()},
				dataType:'json',
				success:function(result){
					if(!result.datas.error){
						if(typeof(result.datas.key)=='undefined'){
							return false;
						}else{
                            // 更新cookie购物车
                            updateCookieCart(result.datas.key);
							addCookie('username',result.datas.username);
							addCookie('key',result.datas.key);
							location.href = WapSiteUrl+'/tmpl/member/member.html';
						}
		                errorTipsHide();
					}else{
		                errorTipsShow("<p>"+result.datas.error+"</p>");
		                register_member = 0;
					}
				}
			});
		}
	});
});
