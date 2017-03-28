'use strict';

$(function(){
    // 1. 点击复选框
    $(".check").click(function(e) {
        $(this).toggleClass("checked");
        $(this).next().toggleClass("checked_info");
        if ($(this).hasClass("checked")) {
            $('#agreed').parent().next().find("span").hide();
        }
        e.preventDefault();
        e.stopPropagation();
    });
    // 2. 切换到邮箱注册Tab页
    $("#mail_register").click(function(e) {
        $("#mail_register").addClass("tab_current");
        $("#mail_register_body").show();
        $("#phone_register").removeClass("tab_current");
        $("#phone_register_body").hide();
        e.preventDefault();
        e.stopPropagation();
    });
    // 3. 切换到手机注册Tab页
    $("#phone_register").click(function(e) {
        $("#mail_register").removeClass("tab_current");
        $("#mail_register_body").hide();
        $("#phone_register").addClass("tab_current");
        $("#phone_register_body").show();
        e.preventDefault();
        e.stopPropagation();
    });
    // 4. 获取验证码
    $("#get_verifier_btn").click(function(e) {
        if (verifier_seconds < 60) {
            return;
        } else {
            if ($("#phone_number").val() == null) {
                $("#phone_number").focus();
            }
            // a. 暂时禁用此按钮
            $("#get_verifier_btn").html("获取验证码(" + (verifier_seconds--) + ")");
            // b. 发送验证码
            $.ajax({
                type: "GET",
                url: URL + "/?ac=validate_mobile&op=reg&_t=" + new Date().getTime() + "&mobile=" + $("#phone_number").val(),
                async: true,
                cache: false,
                dataType: "json",
                error: function(data){
                    if (_interval) {
                        clearInterval(_interval);
                    }
                    $("#get_verifier_btn").html("发送失败，点击重试");
                }
            });
            // c. 60秒后恢复按钮功能
            _interval = setInterval("recoverGetVerifierBtn()", 1000);
        }
        e.preventDefault();
        e.stopPropagation();
    });
    // 5. 鼠标点击用户名框
    $("#js-username").on("focus", function(e) {
        if ($.trim($(this).val()) == '用户名') {
            $(this).val("");
        }
        e.preventDefault();
        e.stopPropagation();
    }).on("blur", function(e) {
        if ($(this).val() == '') {
            $(this).val("用户名");
        }
        e.preventDefault();
        e.stopPropagation();
    });
    // 6. 鼠标点击用密码框
    $("#js-password").on("focus", function(e) {
        if ($(this).val() == '') {
            $(this).val("");
        }
        e.preventDefault();
        e.stopPropagation();
    }).on("blur", function(e) {
        if ($(this).val() == '') {
            $(this).val("");
        }
        e.preventDefault();
        e.stopPropagation();
    });
    // 7. 用户名注册
    $("#registerBtn").on("click", function (){
        if($('#agreed').hasClass('checked')){
            //if($.trim($('#username').val()).length < 5 || $.trim($('#username').val()).length > 20){
            //alert('用户账号长度范围为5~20个字符！');
            if($.trim($('#username').val()).match(/^([a-z0-9_]{6,20})$/g)== null){
                $('#username').parent().next().find("span").show();
                $('#username').focus(); 
                return false;
            }else if($.trim($('#pwd').val()).length < 6 || $.trim($('#pwd').val()).length > 20
                    || $.trim($('#pwd').val()) == $.trim($('#username').val())){
                $('#pwd').parent().next().find("span").show();
                return false;
            }else if($.trim($('#pwd2').val()) == ""){
                $('#pwd2').parent().next().find("txt").html("请输入确认密码！");
                $('#pwd2').parent().next().find("span").show();
                $('#pwd2').focus();
                return false;
            }else if($.trim($('#pwd').val()) != $.trim($('#pwd2').val())){
                $('#pwd2').parent().next().find("txt").html("两次输入的密码不一致！");
                $('#pwd2').parent().next().find("span").show();
                return false;
            /*}else if($.trim($('#realname').val()) == '' || $.trim($('#idcard').val()) == ''){
                alert('您的身份证信息不得为空！');
                $('#realname').focus(); 
                return false;
             }else if($.trim($('#realname').val()).match(/^[\u4e00-\u9fa5]*$/g) == null){ 
                alert("真是姓名必须为中文！") 
                $('#realname').val('');
                $('#realname').focus(); 
                return false;
            }else if(!IdCardValidate($.trim($('#idcard').val()))){
                alert('您的身份证号码填写有误！');
                $('#idcard').focus();
                return false;*/
            }else if($.trim($('#email').val()) != "" && !/\w@\w*\.\w/.test($.trim($('#email').val()))){
                $('#email').parent().next().find("span").show();
                $('#email').focus();
                return false;
            }else{
                $("#register_btn").unbind("click");
                $("[name=mail_register_form]").submit();
                return true;
            }
        }else{
            $('#agreed').parent().next().find("span").show();
            return false;
        }
    });
    // 8. 文本框内容改变
    $("#username, #pwd, #pwd2, #email, #phone_number, #phone_pwd, #phone_pwd2, #verifier").on("change", function() {
        $(this).parent().next().find("span").hide();
    });
    // 9. 手机注册
    $("#phone_register_btn").on("click", function (){
        if($('#phone_agreed').hasClass('checked')){
            if($.trim($('#phone_number').val()) == null
                    || !$.trim($('#phone_number').val()).match(/^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/)){
                $('#phone_number').parent().next().find("span").show();
                $('#phone_number').focus();
                return false;
            }/*else if($.trim($('#verifier').val()).length != 6){
                $('#verifier').parent().next().find("span").show();
                return false;
            }*/else if($.trim($('#phone_pwd').val()).length < 6 || $.trim($('#phone_pwd').val()).length > 20
                    || $.trim($('#phone_pwd').val()) == $.trim($('#phone_number').val())){
                $('#phone_pwd').parent().next().find("span").show();
                return false;
            }else if($.trim($('#phone_pwd2').val()) == ""){
                $('#phone_pwd2').parent().next().find("txt").html("请输入确认密码！");
                $('#phone_pwd2').parent().next().find("span").show();
                $('#phone_pwd2').focus();
                return false;
            }else if($.trim($('#phone_pwd').val()) != $.trim($('#phone_pwd2').val())){
                $('#phone_pwd2').parent().next().find("txt").html("两次输入的密码不一致！");
                $('#phone_pwd2').parent().next().find("span").show();
                return false;
            }else{
                $("#phone_register_btn").unbind("click");
                $("[name=phone_register_form]").submit();
                return true;
            }
        }else{
            $('#phone_agreed').parent().next().find("span").show();
            return false;
        }
    });
});

var _interval = null, verifier_seconds = 60;
function recoverGetVerifierBtn() {
    if (verifier_seconds > 0) {
        $("#get_verifier_btn").html("获取验证码(" + (verifier_seconds--) + ")");
    } else {
        $("#get_verifier_btn").html("获取验证码");
        verifier_seconds = 60;
        clearInterval(_interval);
    }
}

//-----------------------以下是登录页面相关代码---------------------------
$(function(){
    // 1. 点击登录按钮
    $("#login_btn").click(function(){
        $("[name=login_form]").submit();
    });
});
