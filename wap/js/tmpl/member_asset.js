$(function(){
    var key = getCookie('key');
    if (!key) {
        window.location.href = WapSiteUrl + '/tmpl/member/login.html';
        return;
    }
    $.getJSON(ApiUrl + '/index.php?act=member_index&op=my_asset', {key:key}, function(result){
        checkLogin(result.login);
        $('#predepoit').html(result.datas.predepoit+' 元');
        $('#point2').html(result.datas.member_points2);
        $('#point').html(result.datas.point);
    });
});