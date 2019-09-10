$(function () {

    var uid = window.location.href.split("#V5");
    var fragment = uid[1];
    if (fragment) {
        if (fragment.indexOf("V5") == 0) {
            addCookie("uid", "0");
        } else {
            addCookie("uid", fragment);
        }
    }


    var key = getCookie('key');
    if (key) {
        window.location.href = WapSiteUrl + '/tmpl/member/member.html';
        return;
    }
    $.getJSON(ApiUrl + '/index.php?act=connect&op=get_state&t=connect_sms_reg', function (result) {
        if (result.datas != '0') {
            $('.register-tab').show();
        }
    });

    $.sValid.init({//注册验证
        rules: {
            username: "required",
            phone: "required"
        },
        messages: {
            username: "用户名必须填写！",
            phone: "手机必填!"
        },
        callback: function (eId, eMsg, eRules) {
            if (eId.length > 0) {
                var errorHtml = "";
                $.map(eMsg, function (idx, item) {
                    errorHtml += "<p>" + idx + "</p>";
                });
                errorTipsShow(errorHtml);
            } else {
                errorTipsHide();
            }
        }
    });
    var register_member = 0;
    $('#registerbtn').click(function () {
        if (!$(this).parent().hasClass('ok')) {
            return false;
        }
        if (!$('#checkbox').prop('checked')) {
            errorTipsShow("请同意用户注册协议和玖久国际商城代售协议书");
            return false;
        }
        var username = $("input[name=username]").val();
        // var pwd = $("input[name=pwd]").val();
        var password = $("input[name=password]").val();
        var password_confirm = $("input[name=password_confirm]").val();
        var paypwd = $("input[name=paypwd]").val();
        var paypassword_confirm = $("input[name=paypassword_confirm]").val();
        var phone = $("input[name=phone]").val();
        var client = 'wap';
        var code = $('#code').val();
        if (register_member) {
            errorTipsShow("<p>正在处理中，请勿重复点击！</p>");
            return false;
        }
        if(password != password_confirm){
            layer.open({
                content: '密码不一致'
                , skin: 'msg'
                , time: 2 //2秒后自动关闭
            });
            return false;
        }
        if ($.sValid()) {
            register_member = 1;
            $.ajax({
                type: 'post',
                url: ApiUrl + "/index.php?act=login&op=register",
                data: {
                    username: username,
                    paypassword: paypwd,
                    paypassword_confirm: paypassword_confirm,
                    phone: phone,
                    client: client,
                    recmember: $('#recmember').val(),
                    nodemember: $('#nodemember').val(),
                    code: code,
                    password: password,
                    password_confirm: password_confirm
                },
                dataType: 'json',
                success: function (result) {
                    if (!result.datas.error) {
                        if (typeof(result.datas.key) == 'undefined') {
                            return false;
                        } else {
                            // 更新cookie购物车
                            updateCookieCart(result.datas.key);
                            addCookie('username', result.datas.username);
                            addCookie('key', result.datas.key);
                            location.href = WapSiteUrl + '/tmpl/member/member.html';
                        }
                        errorTipsHide();
                    } else {
                        errorTipsShow("<p>" + result.datas.error + "</p>");
                        register_member = 0;
                    }
                }
            });
        }
    });
    var getCode = function () {
        var regex = /^((\+)?86|((\+)?86)?)0?1[3458]\d{9}$/;
        var phone = $("input[name='phone']").val();
        if (phone.match(regex) == null) {
            layer.open({
                content: '手机号不正确'
                , skin: 'msg'
                , time: 2 //2秒后自动关闭
            });
            return false;
        }
        sendCode(phone);  //发送验证码
    };
    $('#get_code').on('click', getCode);

    //发送验证码  "{:U('AuthGroup/addChildGroup')}"
    function sendCode(phone) {
        var sendUrl = ApiUrl + "/index.php?act=login&op=sendMobileCode";
        var is_status = false;
        var wait = 10;
        var t_img;
        $.ajax({
            url: sendUrl,
            dataType: 'json',
            type: 'post',
            data: {phone: phone},
            cache: false,
            async: true,
            success: function (json) {
                console.log(json);
                if (json.code == 200) {  //no-click
                    layer.open({
                        content: '发送成功'
                        , skin: 'msg'
                        , time: 2
                    });
                    $('#get_code').addClass('layui-btn-disabled').removeClass('layui-btn-normal');
                    time_run()
                } else {
                    layer.open({
                        content: json.datas.error
                        , skin: 'msg'
                        , time: 2
                    });
                }
            },
            error: function () {
                layer.open({
                    content: '网络错误,请稍后再试'
                    , skin: 'msg'
                    , time: 2
                });
            }
        });

        function time_run() {
            var obj = $('#get_code');
            if (wait == 0) {
                is_status = true;
            }
            if (is_status) {
                clearTimeout(t_img); // 清除定时器
                obj.on('click', getCode);  //绑定click
                obj.addClass('layui-btn-normal').removeClass('layui-btn-disabled');
                $('#get_code').html('发送验证码');
            } else {
                is_status = false;
                obj.off('click');  //移除click
                $('#get_code').html(wait + 's后重新获取');
                wait--;
                t_img = setTimeout(function () {
                    time_run();
                }, 1000);
            }
        }
    }

    //匹配url参数值
    function getUrlParam(paras) {
        var url = location.href;
        var paraString = url.substring(url.indexOf("?") + 1, url.length).split("&");
        var paraObj = {};
        for (i = 0; j = paraString[i]; i++) {
            paraObj[j.substring(0, j.indexOf("=")).toLowerCase()] = j.substring(j.indexOf("=") + 1, j.length);
        }
        var returnValue = paraObj[paras.toLowerCase()];
        if (typeof(returnValue) == "undefined") {
            return "";
        } else {
            return returnValue;
        }
    }

    //处理扫描二维码的注册方式
    function matchUrlMemberId() {
        var member_id = getUrlParam('member_id');
        if (member_id > 0) {
            //获取邀请人的基本信息
            $.ajax({
                url: ApiUrl + "/index.php?act=login&op=getMemberInfoByMemberId",
                dataType: 'json',
                type: 'post',
                data: {member_id: member_id},
                cache: false,
                async: true,
                success: function (json) {

                    if (json.code == 200) {  //no-click
                        $('#recmember').val(json.datas.recmember);
                        $('#nodemember').val(json.datas.nodemember);
                    }
                },
                error: function () {
                    layer.open({
                        content: '网络错误,请稍后再试'
                        , skin: 'msg'
                        , time: 2
                    });
                }
            });
        }
    }

    matchUrlMemberId();
});
