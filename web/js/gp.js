var GP = {
	CONFIG:{
		ValiErrorStyle:{
			"suc": "t-suc",
			"err": "t-err"
		}
	}
};

//消息提示
GP.Message = (function(){
    var _temp = '<div class="pop-msg"><i class="icon"></i><span rel="con">%1</span></div>';
    var _dom;
	var _typesStrut = {
		"suc": "i-suc1",
		"load": "i-loading",
		"err": "i-err1",
		"war": "i-err1"
	}
    var _timer;
	
	//创建消息DOM
    var create = function(text,type){
        if(!_dom){
            _dom = $(String.format(_temp,text));
            $(document.body).append(_dom);
			$(window).bind("resize",function(){
				//var top = $(window).scrollTop() + 32;
				var top = $(window).scrollTop() + ((document.documentElement.clientHeight - _dom.height()) /2.5);
				_dom.css({top: top + "px",left:($(document).width() - _dom.width()) / 2 + "px"});
			})
        }
		var top = $(window).scrollTop() + ((document.documentElement.clientHeight - _dom.height()) /2.5);
		_dom.css({top: top + "px",left:($(document).width() - _dom.width()) / 2 + "px"});
		
        _dom.find("[rel='con']").html(text);
        var icon = _dom.find(".icon");
        for(var k in _typesStrut){
            icon.removeClass(_typesStrut[k]);
        }
        icon.addClass(_typesStrut[type]);
    }
	
	//隐藏
    var hide = function(){
        if(_timer){
            window.clearTimeout(_timer);
        }
        if(_dom){
            _dom.hide();
        }
    }

    return {
        Show: function(obj){
            if(!obj.type){
                obj.type = "load";
            }
            create(obj.text,obj.type);
            _dom.show();
            if(_timer){
                window.clearTimeout(_timer);
            }
            if(obj.timeout){
                _timer = window.setTimeout(hide,obj.timeout);
            }
        },
        Hide: function(){
            hide();
        }
    }
})();

//UI控件类
GP.UI = (function(){
	
	var hideErrBox = function(dom){
		var p = dom.parent();
		var errorBox = p.find("[vali_msg='1']");
		if(errorBox.length){
			errorBox.hide();
		}
	}
	
	var createErrBox = function(dom,type,msg){
		//var p = dom.parent();
        var p = (dom.attr("inpType") == "editor") ? dom.parents().find("[elEditor='editor']") : dom.parent();
		var errorBox = p.find("[vali_msg='1']");
		if(!errorBox.length){
			errorBox = $('<span class="tip t-err" style="display:none;" vali_msg="1"></span>');
			p.append(errorBox);
		}
		p.find("[vali_msg='0']").hide();
		if(!type){
			type = "err";
		}
		if(msg){
			errorBox.removeClass(GP.CONFIG.ValiErrorStyle["suc"]).removeClass(GP.CONFIG.ValiErrorStyle["err"]).addClass(GP.CONFIG.ValiErrorStyle[type]).html("<i class='ico'></i>" + msg).show();	
		}
		else{
			errorBox.removeClass(GP.CONFIG.ValiErrorStyle["suc"]).removeClass(GP.CONFIG.ValiErrorStyle["err"]).addClass(GP.CONFIG.ValiErrorStyle[type]).html("<i class='ico'></i>" + dom.attr("error")).show();	
		}
	}

	var initTable = function(){
		$("[table='1']").each(function(){
			var tb = $(this);
			tb.find("input[type='checkbox'][check='parent']").bind("click",function(){
				var childs = tb.find("input[type='checkbox'][check='child']");
				if(childs.length){
					childs.attr("checked",$(this).attr("checked"));
				}
			})
			
			tb.find("[js_type='delete']").click(function(){
				var ele = $(this);
				Util.MsgBox.Confirm({
					text:"确认要删除吗？",
					type:"warm",
					callback:function(r){
						if(r){
							var url = ele.attr("href");
							var list = tb.find("input[type='checkbox'][check='child'][checked=true]");
							var name = ele.attr("js_name");
							if(!name){
								name = "id";
							}
							var data = {};
							list.each(function(i){
								var val = $(this).val();
								data[name+"["+i+"]"] = val;
							});
							$.ajax({
								url:url,
								data:data,
								type:"POST",
								dataType: "json",
								success: function(result){
									if(result.state == true){
										window.location.reload();
									}
									else if(result.state == "noid"){
										GP.Message.Show({text:"请选择要删除的信息",type:"err",timeout:2000});
                                                                                //window.location.reload();
									}
                                    else{
                                          GP.Message.Show({text:"删除失败",type:"err",timeout:2000});
                                    }
								}
							})
						}
					}
				})
				return false;
			})
		})
	}

	var initForm = function(){
		$("form[vali]").each(function(){
			var form = $(this);
            var isError = false;
			//绑定提交事件
			Util.Validate.BindForm(this,{
				ErrorCallBack: function(error){
					form.find("[vali]").each(function(){
                        var dom = ($(this).attr("inpType") == "editor") ? form.find("[elEditor='editor']") : $(this).parent();
						if($(this).attr("ajaxstate") == "-1" || $(this).attr("ajaxstate") == "1"){
							dom.find("[vali_msg='1']").show();
							dom.find("[vali_msg='0']").hide();
						}
						else{
							dom.find("[vali_msg='0']").hide();
							dom.find("[vali_msg='1']").show();
						}
					});
					
					for(var k in error){
						var dom = $(error[k]);
						if(dom.attr("ajaxstate") == "-1" || dom.attr("ajaxstate") == "1"){
							continue;
						}
						else{
							createErrBox(dom);
						}
					}
				},
				//返回结果后回调
				ReturnCallBack: function(ele,state){
					var input = ele.find("[rate]");
					var inputParent = $("#" + input.attr("rate"));
					if(inputParent.length){
						//对比两次输入是否一样
						if(input.val() != inputParent.val()){
							createErrBox(input);
							if(state){
								state = false;
							}
						}
					}
					input = ele.find("[ajaxstate='-1']");
					if(input.length){
						state = false;
					}
					
					if(ele.attr("onreturn")){
						var r = eval(ele.attr("onreturn"));
						if(!r){
							state = r;
						}
					}
					
					if(r){
						if(ele.attr("success")){
							eval(ele.attr("success"));
						}
					}
					return state;
				}
			});
            
            var _required = function(el,typeArr){   // VALIDATE BLANK FIELD
                var callerType = $(el).attr("type");
                var el = $(el);
                if (callerType == "text" || callerType == "password" || callerType == "textarea"){                        
                    for(var j = 0,jlen = typeArr.length; j < jlen; j++){
                        if(!(Util.Validate.Check(typeArr[j],el.val()) || (el.attr("require") == "0" && $.trim(el.val()) == ""))){
                            isError = true;
                            break;
                        }
                        else{
                           isError = false; 
                        }
                        if(typeArr[j] == "notempty"){
                            var min = el.attr("min") ? Number(el.attr("min")) : 0;
                            var max = el.attr("max") ? Number(el.attr("max")) : -1;
                            var count = $.trim(this.value).length;
                            if(!(min <= count && (max == -1 || max >= count))){
                                isError = true;
                                break;
                            }
                        }
                        else{
                           isError = false; 
                        }
                    }
                    
                }	
                if (callerType == "radio" || callerType == "checkbox" ){
                    callerName = el.attr("name");
                    if($("input[name='"+callerName+"']:checked").size() == 0){
                        isError = true;
                    }
                    else{
                        isError = false;
                        el.parent().find("[vali_msg='1']").hide();
                        el.parent().find("[vali_msg='0']").show();
                    }
                }	
                if (callerType == "select-one") { // added by paul@kinetek.net for select boxes, Thank you		
                    if(!el.val()) {
                        isError = true;
                    }
                    else{
                       isError = false;  
                    }
                }
                // 创建提示信息
                isError ? createErrBox(el) : createErrBox(el,"suc"," ");
                //ajax
                if(!isError && el.attr("ajax")){
                    el.attr("ajaxstate","0");
                    var data = {};
		    var ajax_url = el.attr("ajax");
		    ajax_url += (ajax_url.indexOf('?') > -1 ? '&' : '?') + '_t='+(new Date()).getTime();
                    data[el.attr("name")] = el.val();
                    $.ajax({
                        url: ajax_url,
                        type: "POST",
                        data: data,
                        caceh: false,
                        success: function(r){
                            if($.trim(r) == "success"){
                                createErrBox(el,"suc",el.attr("ajax_suc_msg"));
                                el.attr("ajaxstate","1");
                            }
                            else{
                                createErrBox(el,"err",r);
                                el.attr("ajaxstate","-1");
                            }
                        }
                    })
                }
            }
			
		//绑定光标事件
		form.find("[vali]").each(function(i){
		var ele = $(this);
                var type = ele.attr("type");
                var typeArr = ele.attr("vali").split("|");
                
                if(type == "checkbox"){
                    ele.bind("click",function(){
                        _required(this,typeArr);
                    });
                }
                else{
                    ele.bind("blur",function(){
                        _required(this,typeArr);
                    }).bind("focus",function(){
                        var p = (ele.attr("inpType") == "editor") ? form.find("[elEditor='editor']") : ele.parent();
                        p.find("[vali_msg='1']").hide();
                        p.find("[vali_msg='0']").show();
                    });
                }
			})
		});
	}
	
	return {
		Form: {
			CreateError: function(dom,type,msg){
				createErrBox(dom,type,msg);
			},
			HideError: function(dom){
				hideErrBox(dom);
			}
		},
		Init: function(){
			//初始化
			initTable();
			initForm();
		}
	}
})();

GP.Send = (function(){
	return {
		SendSubmit: function(){
			var state = true;
			var msg_body = $("#msg_body").val().replace("\n","");
			if(!Util.Validate.Check("notempty",msg_body) || msg_body.length < 1 || msg_body.length > 1150){
				GP.UI.Form.CreateError($("#msg_body"),"err","请输入1-1150个字符的内容");
				state = false;
			}
			else{
				var bodyP = $("#msg_body").parent();
				bodyP.find("[vali_msg='1']").hide();
				bodyP.find("[vali_msg='0']").show();
			}
			return state;
		},
		Msg: function(toUserId,toUserName,subject){
			var html = '<form id="js_send_msg_box"><table cellspacing="0" cellpadding="0" border="0" class="form" style="margin:10px 0;"><tr><th>收件人：</th><td><i>%2</i><input type="hidden" value="%1" id="friend_uid" name="to_user_id"></td></tr><tr><th>标题：</th><td><input type="text" id="msg_subject" name="subject" class="text" /> <span class="gray">标题可以不需要填写</span></td></tr><tr><th>内容：</th><td><textarea id="msg_body" name="body"></textarea><span vali_msg="0" class="gray">1150字符以内</span></td></tr><tr><th>&nbsp;</th><td><button type="submit" class="active">立即发送</button>&nbsp;<button type="button" class="bt" onclick="Util.ScreenManager.Hide();">取消</button></td></tr></table></form>';
			html = String.format(html,toUserId,toUserName);
			Util.MsgBox.Show({
				text: html,
				title: "发送新消息",
				width: 600
			})
			document.getElementById('msg_body').focus();
			$("#js_send_msg_box").submit(function(){
				if(GP.Send.SendSubmit()){
					var data = {};
					var ele = $(this);
					ele.find("[name]").each(function(){
						data[$(this).attr("name")] = $(this).val();
					});
					
					if(data['subject'] == ''){
						var title = "";
						if(data['body'].length > 20){
							title = data['body'].substring(0,20);
						}
						else{
							title = data['body'].replace("\n","");
						}
						data['subject'] = title;
					}
					$.ajax({
						url: "?ct=message&ac=ajax_send",
						type:"POST",
						data:data,
						dataType: "json",
						success: function(r){
							if(r.state){
								GP.Message.Show({
									text: "发送成功",
									type: "suc",
									timeout: 2000
								})
								Util.ScreenManager.Hide();
							}
							else{
								alert(r.msg);
							}
						}
					})
				}
				return false;
			})
			if(subject){
				document.getElementById('msg_subject').value = subject;
			}
		}
	}
})();

$(document).ready(function(){
	GP.UI.Init();
});