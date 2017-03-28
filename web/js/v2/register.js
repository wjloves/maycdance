
'use strict';

$(function() {
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
                    $("#get_verifier_btn").html("发送失败");
                }
            });
            // c. 60秒后恢复按钮功能
            _interval = setInterval("recoverGetVerifierBtn()", 1000);
        }
        e.preventDefault();
        e.stopPropagation();
    });

    var registerRequires = {
        //errorClass: "error", //默认为错误的样式类为：error
        focusInvalid: false, //当为false时，验证无效时，没有焦点响应
        onkeyup: false,
        submitHandler: function(form){
            form.submit();   //提交表单
        },
        rules: {
            username : {
                required: true
            },
            pwd: {
                required: true,
                isUuid:true
            },
            pwdconfirm: {
                required: true,
                equalTo: "#pwd"
            },
            mail: {
                required: true,
                email:true
            },
            checkbox: "required"
        },
        messages: {
            pwd: {
                isUuid: "请输入3到15位的数字或字母的密码"
            },
            pwdconfirm: {
                equalTo: "两次输入密码不一致"
            },
            checkbox: "请阅读服务协议隐私条款"
        },
        errorPlacement: function(error, element) {
            console.log(error)
            if ( element.is(":radio") ) {
                error.appendTo(element.parent().next().next());
            }else if ( element.is(":checkbox") ) {
                error.appendTo(element.parent());
            }else {
                error.appendTo(element.parent().next());
            }
        }
    };

    $('#registerBtn').on('click',function(e){
        $("#registerForm").validate(registerRequires).form();
    });


    var registerRequires_phone = {
        //errorClass: "error", //默认为错误的样式类为：error
        focusInvalid: false, //当为false时，验证无效时，没有焦点响应
        onkeyup: false,
        submitHandler: function(form){
            form.submit();   //提交表单
        },
        rules: {
            phone : {
                required: true
            },
            verifier: {
                required: true
            },
            pwd: {
                required: true,
                isUuid:true
            },
            pwdconfirm: {
                required: true,
                equalTo: "#phone_pwd"
            },
            mail: {
                required: true,
                email:true
            },
            checkbox: "required"
        },
        messages: {
            pwd: {
                isUuid: "请输入3到15位的数字或字母的密码"
            },
            pwdconfirm: {
                equalTo: "两次输入密码不一致"
            },
            checkbox: "请阅读服务协议隐私条款"
        },
        errorPlacement: function(error, element) {
            console.log(error)
            if ( element.is(":radio") ) {
                error.appendTo(element.parent().next().next());
            }else if ( element.is(":checkbox") ) {
                error.appendTo(element.parent());
            }else {
                error.appendTo(element.parent().next());
            }
        }
    };

    $('#phone_registerBtn').on('click',function(e){
        $("#registerForm_phone").validate(registerRequires_phone).form();
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