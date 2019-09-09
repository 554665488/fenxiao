var key = getCookie('key');
var payment_code;
 // 现在支付方式
 function toPay(pay_sn,act,op) {
     $.ajax({
         type:'post',
         url:ApiUrl+'/index.php?act='+act+'&op='+op,
         data:{
             key:key,
             pay_sn:pay_sn
             },
         dataType:'json',
         success: function(result){
             checkLogin(result.login);
             if (result.datas.error) {
             	layer.open({content: result.datas.error,skin: 'msg' ,time: 2});
                 return false;
             }
             // 从下到上动态显示隐藏内容
             $.animationUp({valve:'',scroll:''});
             
             // 需要支付金额
             $('#onlineTotal').html(result.datas.pay_info.pay_amount);

             payment_code = '';
             if (!$.isEmptyObject(result.datas.pay_info.payment_list)) {
                 var readytoWXPay = false;
                 var readytoAliPay = false;
                 var m = navigator.userAgent.match(/MicroMessenger\/(\d+)\./);
                 if (parseInt(m && m[1] || 0) >= 5) {
                     // 微信内浏览器
                     readytoWXPay = true;
                 } else {
                     readytoAliPay = true;
                 }
                 for (var i=0; i<result.datas.pay_info.payment_list.length; i++) {
                     var _payment_code = result.datas.pay_info.payment_list[i].payment_code;
                     if (_payment_code == 'alipay' && readytoAliPay) {
                         $('#'+ _payment_code).parents('label').show();
                         if (payment_code == '') {
                             payment_code = _payment_code;
                             $('#'+_payment_code).attr('checked', true).parents('label').addClass('checked');
                         }
                     }
                     if (_payment_code == 'wxpay_jsapi' && readytoWXPay) {
                         $('#'+ _payment_code).parents('label').show();
                         if (payment_code == '') {
                             payment_code = _payment_code;
                             $('#'+_payment_code).attr('checked', true).parents('label').addClass('checked');
                         }
                     }
                 }
             }

             $('#alipay').click(function(){
                 payment_code = 'alipay';
             });
             
             $('#wxpay_jsapi').click(function(){
                 payment_code = 'wxpay_jsapi';
             });

             $('#toPay').click(function(){
                 if (payment_code == '') {
                     layer.open({content: '请选择支付方式',skin: 'msg' ,time: 2});
                     return false;
                 }
                goToPayment(pay_sn);
                 
             });
         }
     });
 }

 function goToPayment(pay_sn) {
     location.href = ApiUrl+'/index.php?act=member_payment&op=pay_new&key=' + key + '&pay_sn=' + pay_sn + '&pdr=1&payment_code=' + payment_code;
 }
