'use strict';

$(function(){
	// 1. 第一步中的“下一步”按钮
	$("#step1_next_btn").click(function(e) {
        if($.trim($('#email').val()) == ""){
            $('#email').next().html("请您输入用户名/手机号");
            $('#email').focus(); 
            return false;
        }else if($.trim($('#vdcode').val()) == ""
                || !isValidVerifier($('#vdcode').val())){
            $('#vdcode').next().next().html("请您输入正确的验证码");
            $('#vdcode').focus(); 
            return false;
        }else{
            $("[name=step1_form]").submit();
            return true;
        }
	});
    // 2. 鼠标点击账号文本框
	$("#email").on("focus", function(e) {
		if ($(this).val() == '请您输入用户名/手机号') {
			$(this).val("");
		}
		e.preventDefault();
		e.stopPropagation();
    }).on("blur", function(e) {
		if ($(this).val() == '') {
			$(this).val("请您输入用户名/手机号");
		}
		e.preventDefault();
		e.stopPropagation();
    });
    
    
    // 1. 发邮件验证码
    $("#send_mail").click(function(e) {
        $.ajax({
            type: "GET",
            url: URL + "/?ct=index&ac=validate_mail",
            data: "op=forget_password&_t=" + $("[class=content]").attr('_t'),
            async: false,
            cache: false,
            dataType: "json",
            success: function(json){
                if (json.state) {
                    $("#mail_tip").html("验证码已成功发送到您的电子邮箱，请查收！");
                } else {
                    $("#mail_tip").html(json.data.message);
                }
            }
        });
        
    });
	// 2. 发手机验证码
    $("#send_sms").click(function(e) {
        if (verifier_seconds < 60) {
            return;
        } else {
            if ($("[name=sms_verifier]").val() == null) {
                $("[name=sms_verifier]").val().focus();
                return;
            }
            // a. 暂时禁用此按钮
            $("#send_sms").html("发送验证码(" + (verifier_seconds--) + ")");
            // b. 发送验证码
            $.ajax({
                type: "GET",
                url: URL + "/?ac=validate_mobile&op=forget_password&mobile=" + $("[name=sms_verifier]").val(),
                async: true,
                cache: false,
                dataType: "json",
                success: function(json){
                    if (json.state) {
                        $("#phone_tip").html("验证码已成功发送到您的手机，请查收！");
                    } else {
                        $("#phone_tip").html(json.data.message);
                    }
                },
                error: function(json){
                    if (_interval) {
                        clearInterval(_interval);
                    }
                    $("#phone_tip").html("发送失败，点击重试");
                }
            });
            // c. 60秒后恢复按钮功能
            _interval = setInterval("recoverGetVerifierBtn('#send_sms')", 1000);
        }
        e.preventDefault();
        e.stopPropagation();
    });
	// 3. 下一步
    $("#step2_next_btn").click(function(e) {
        $("[name=step2_form]").attr('action', $("[name=step2_form]").attr('action')+"&_t="+$("[class=content]").attr('_t'));
		$("[name=step2_form]").submit();
        return true;
    });
	// 4. mail_verifier onchange
	$("[name=mail_verifier]").on("change", function(e) {
		if ($(this).val() != "") {
			$("[name=sms_verifier]").val("");
		}
	});
	// 5. sms_verifier onchange
	$("[name=sms_verifier]").on("change", function(e) {
		if ($(this).val() != "") {
			$("[name=mail_verifier]").val("");
		}
	});
    
    // 6. 下一步
    $("#step3_next_btn").click(function(e) {
		$("[name=step3_form]").submit();
        return true;
    });
    
});

// 验证码输入是否正确
function isValidVerifier() {
    var result = false;
    $.ajax({
        type: "GET",
        url: URL + "/?ct=ajax&ac=get_ckstr",
        data: "validate=" + $("#vdcode").val(),
        async: false,
        cache: false,
        dataType: "text",
        success: function(data){
            if (data && data != "") {
                data = data.substring(1, data.length - 1);
                data = $.parseJSON(data);
                if(data.state){
                    result = true;
                }
            }
        }
    });
    return result;
}

var _interval = null, verifier_seconds = 60;
function recoverGetVerifierBtn(btnId) {
    if (verifier_seconds > 0) {
        $(btnId).html("发送验证码(" + (verifier_seconds--) + ")");
    } else {
        $(btnId).html("发送验证码");
        verifier_seconds = 60;
        clearInterval(_interval);
    }
}
