/**
 * @author 蓝面小生
 */
var ShowAddFileWindow = function(){
    POP_SelectFile();
}
$(function(){
    $(".topic-type").simpleForm();
    $("input[name='subject']").focus();
    EditorControl.Set($("#js_hidden_content").val());
    var noClick = true;
    var validateForm = function(){
        var state = true;
        var subject = $("input[name='subject']"), price = $("input[name='price']"), textVali = $("input[name='txtVali']"), content = EditorControl.Get();
		if( is_must_use_category == 1 && $("#category").val() =="" ){
			 state = false;
			 Y.alertTip("请选择话题分类！");
		}
        else if (subject.val().length < 1) {
            state = false;
            Y.alertTip("请输入标题！");
            subject.focus();
        }
        else 
            if (subject.val().length > 1 && $.trim(subject.val()).length < 1) {
                state = false;
                Y.alertTip("只输入空格可是不好的行为！");
            }
			else if(subject.val().length>50||subject.val().length<4){
				 state = false;
                 Y.alertTip("请输入4到50之间的字符！");
			}
            else 
                if (content.length < 1) {
                    state = false;
                    Y.alertTip("请输入话题内容！");
                }
                else 
                    if (textVali[0] && $.trim(textVali.val()).length != 4) {
                        state = false;
                        Y.alertTip("请输入有效的验证码！");
                        textVali.focus();
                    }
        return state;
    }
    $("#editor_editor .opt .file").click(function(){
        POP_SelectFile();
        return false;
    });
    $("#js_add_file").unbind("click").bind("click", function(){
        POP_SelectFile();
        return false;
    });
    FileSort.init("js_file_list");
    if (is_member != "1") {
        var d = $('<div class="confirm-msg"><p>您现在还不是圈子<b class="orange">&lt;'+group_name+'&gt;</b>的成员</p><p>加入后即可顺利操作...</p></div>').dialog({
            buttons: {
                "现在加入": function(){
                    window.location.href = group_url+"/join?from=IN";
                },
                "取消": function(){
                    d.data("widget_dialog").destroy();
                }
            },
            title: "加入圈子"
        });
    }
    else {
        $("#js_submit_topic").click(function(){
           	formSubmit();
			return false;
        });
    }
    $("#js_ifrHtmlEditor").css("border",0);
	var formSubmit=function(){
		 if (noClick) {
				$("#fids").val(FileSort.getFileSort());
                if (validateForm()) {
                    noClick = false;

					//$.local.set("saveTopic","");


                    $("form").submit();
                }
                else {
                    noClick = true;
                }
            }
            else {
                noClick = false;
            }
	}
	QZ.previewTopic=function(){
		Y.loadTip("预览加载中....")
		Y.log(EditorControl.Get())
	}
	
	QZ.previewPage=function(){
		QZ.contentForPage=EditorControl.Get();
		if(QZ.contentForPage.length<1){
			Y.alertTip("请先输入话题内容!");
			return;
		}
		var d=$.iframe({
			url:"/static/resources/post_index/submit_for_page.html",
			width:700,
			height:400,
			title:"预览",
			buttons:{
				"返回继续编辑":function(){
					d.destroy();
				},
				"发表":function(){
					formSubmit();
				}
			}
		});
	}
});
