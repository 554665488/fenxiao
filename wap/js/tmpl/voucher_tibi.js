//增值券提币 33 hao v 5 .6
$(function() {

	var e = getCookie("key");

	if (!e) {

		window.location.href = WapSiteUrl + "/tmpl/member/login.html";

		return ;

	}


	$.sValid.init({

		rules: {
			a_amount:"required"

		},

		messages: {
			a_amount:"请输入数量！"

		},

		callback: function(e, r, a) {

			if (e.length > 0) {

				var c = "";

				$.map(r, function(e, r) {

					c += "<p>" + e + "</p>";

				});

				errorTipsShow(c);

			} else {

				errorTipsHide();

			}

		}

	});

	$("#saveform").click(function() {

		if (!$(this).parent().hasClass("ok")) {

			return false;

		}

		if ($.sValid()) {
			var a_amount = $.trim($('#a_amount').val());
			var a_address = $.trim($('#a_address').val());
			var key = e;
			if (!key) {
				window.location.href = WapSiteUrl + "/tmpl/member/login.html";
				return ;
			}
			var client = 'wap'; 

			$.ajax({

				type: "post",

				url:ApiUrl+"/index.php?act=member_voucher&op=voucher_tibi",	

				data:{a_amount:a_amount,a_address:a_address,key:key,client:client},

				dataType: "json",
				success:function(result){
					console.log(result)
					if(!result.datas.error){
						alert('提币成功')
						//location.href = 'rechargeinfo.html?paysn='+result.datas.pay_sn;
						
					}else{
						 errorTipsShow("<p>"+result.datas.error+"<p>");
					}
				}

			});

		}

	});

});