/**
 * author:蓝面小生
 * date:2011-07-26
 * mail:gxll1314@gmail.com
 * base simple-ui https://simple-ui.googlecode.com/svn/trunk
 */
//帖子页处理函数
//import common.js Y.js
document.domain = "115.com";
(function(){
    //声明便捷方法
    Y.mixin(QZ, {
        dealReportTemp: "",//缓存举报模板
        dealReportTempAjax: false,//缓存举报的ajax
        dealReportTempUrl: "/static/resources/post_index/circle_report.html",
        dealTemp: "",//缓存普通模板
        dealTempAjax: false,//缓存ajax
        dealTempUrl: "/static/resources/post_index/circle_top.html",
        dealTempMoneyUrl: "/static/resources/post_index/circle_money.html",
        gId: $("#gId").val(),
        tId: $("#tId").val(),
        buyMoney: $("#buyMoney").val(),
        URL_OS: $("#urlOs").val(),
        ownerId: $("#ownerId").val(),
        baseUrl: $("#baseUrl").val(),
        getTime: function(){
            var date = new Date();
            return date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate() + " " + date.getHours() + ":" + date.getMinutes();//+ ":" + date.getSeconds();
        }
    });
    //让textarea自动撑高
    $.fn.autoTextarea = function(options){
        var defaults = {
            maxHeight: null,//文本框是否自动撑高，默认：null，不自动撑高；如果自动撑高必须输入数值，该值作为文本框自动撑高的最大高度
            minHeight: $(this).height() //默认最小高度，也就是文本框最初的高度，当内容高度小于这个高度的时候，文本以这个高度显示
        };
        var opts = $.extend({}, defaults, options);
        return $(this).each(function(){
            $(this).bind("paste cut keydown keyup focus blur", function(){
                var height, style = this.style;
                this.style.height = opts.minHeight + 'px';
                if (this.scrollHeight > opts.minHeight) {
                    if (opts.maxHeight && this.scrollHeight > opts.maxHeight) {
                        height = opts.maxHeight;
                        style.overflowY = 'scroll';
                    }
                    else {
                        height = this.scrollHeight;
                        style.overflowY = 'hidden';
                    }
                    this.style.height = height + "px";
                }
            })
        })
    }
    $(".circle-action").click(function(){
        var self = $(this), rel = self.attr("rel");
        dealMethodClick(rel, QZ.tId);
        return false;
    });
    //解析URL链接
    QZ.formatUrl = function(strTemp, mode){
        if (strTemp) {
            /*strTemp = strTemp.replace(/([^>=\]"'\/]|^)((((https?|ftp):\/\/)|www\.)([\w\-]+\.)*[\w\-\u4e00-\u9fa5]+\.([\.a-zA-Z0-9]+|\u4E2D\u56FD|\u7F51\u7EDC|\u516C\u53F8)((\?|\/|:)+[\w\.\/=\?%\-&~`@':+!]*)+\.(jpg|gif|png|bmp))/ig, mode == 'html' ? '$1<img src="$2" border="0">' : '$1[img]$2[/img]');*/
            strTemp = strTemp.replace(/([^>=\]"'\/@]|^)((((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast):\/\/))([\w\-]+\.)*[:\.@\-\w\u4e00-\u9fa5]+\.([\.a-zA-Z0-9]+|\u4E2D\u56FD|\u7F51\u7EDC|\u516C\u53F8)((\?|\/|:)+[\w\.\/=\?%\-&~`@':+!#]*)*)/ig, mode == 'html' ? '$1<a href="$2" target="_blank">$2</a>' : '$1[url]$2[/url]');
            strTemp = strTemp.replace(/([^\w>=\]"'\/@]|^)((www\.)([\w\-]+\.)*[:\.@\-\w\u4e00-\u9fa5]+\.([\.a-zA-Z0-9]+|\u4E2D\u56FD|\u7F51\u7EDC|\u516C\u53F8)((\?|\/|:)+[\w\.\/=\?%\-&~`@':+!#]*)*)/ig, mode == 'html' ? '$1<a href="$2" target="_blank">$2</a>' : '$1[url]$2[/url]');
            strTemp = strTemp.replace(/([^\w->=\]:"'\.\/]|^)(([\-\.\w]+@[\.\-\w]+(\.\w+)+))/ig, mode == 'html' ? '$1<a href="mailto:$2">$2</a>' : '$1[email]$2[/email]');
        }
        return strTemp;
    }
    /**
     * 负责初始化话题的模板和显示文字
     * @param {Object} rel 操作的类型
     * @param {Object} tid 话题的ID
     */
    var dealMethodClick = QZ.dealMethodClick = function(rel, tid){
        var tempUrl, text, ktext;
        if (rel == "digests") {
            text = "设置精华";
            ktext = "取消精华";
            tempUrl = "/static/resources/post_index/circle_digests.html";
            if (!QZ.dealTempAjaxDigests) {
                $.get(tempUrl, function(result){
                    QZ.dealTempDigests = result;
                    QZ.dealTopic(rel, text, ktext, tid);
                    QZ.dealTempAjaxDigests = true;
                });
            }
            else {
                QZ.dealTopic(rel, text, ktext, tid);
            }
        }
        else {
            if (rel == "top") {
                text = "置顶";
                ktext = "取消置顶";
                QZ.getHtml("circle_top_new", function(r){
                    var dia = $(r).dialog({
                        title: "置顶",
                        buttons: {
                            "确定": function(){
                                QZ.dealTopicByTop(tid, "#s_form");
                            },
                            "取消": function(){
                                dia.data("widget_dialog").destroy();
                            }
                        }
                    })
                })
                return;
            }
            else 
                if (rel == "lock") {
                    text = "锁定";
                    ktext = "取消锁定";
                }
                else 
                    if (rel == "del") {
                        text = "扣除元宝";
                        ktext = "不扣元宝";
                    }
            
            tempUrl = QZ.dealTempUrl;
            if (!QZ.dealTempAjax) {
                $.get(tempUrl, function(result){
                    QZ.dealTemp = result;
                    QZ.dealTopic(rel, text, ktext, tid);
                    QZ.dealTempAjax = true;
                });
            }
            else {
                QZ.dealTopic(rel, text, ktext, tid);
            }
        }
    }
    
    QZ.dealTopicByTop = function(id, form){
        var data = $(form).serializeArray();
        data.push({
            name: "checkboxs",
            value: id
        });
        var currHost = "http://" + location.host, url = currHost + "/" + QZ.gId + "/post/top";
        $.post(url, data, function(r){
            if (r.status) {
                //need fix
                //Util.MsgBox.Show({
                 //   text: r.msg,
                  //  mType: 'suc'
               // });
                Y.successTip(r.msg);
                location.reload();
            }
            else {
               // Util.MsgBox.Show({
               //     text: r.msg,
                //    mType: 'err'
              //  });
                Y.alertTip(r.msg);
            }
        }, "json");
    }
    /**
     * //处理话题置顶，精华，锁帖操作
     * @param {Object} type 处理类型
     * @param {Object} text 操作的显示文字
     * @param {Object} ktext 反向操作的显示文字
     * @param {Object} id 话题的id
     */
    QZ.dealTopic = function(type, text, ktext, id){
        var stringTemp;
        if (type == "digests") {
            stringTemp = Y.util.formatString(QZ.dealTempDigests, {
                key: text,
                skey: ktext
            });
        }
        else {
            stringTemp = Y.util.formatString(QZ.dealTemp, {
                key: text,
                skey: ktext
            });
        }
        if (type == "del") {
            text = "删除"
        }
        dealMethods(stringTemp, text, type, id);
    }
    /**
     * 帖子操作的处理方法
     * @param {Object} temp
     * @param {Object} title
     * @param {Object} type  标识处理的类型
     * @param {string || array} tid 处理话题的ID
     */
    var dealMethods = function(temp, title, type, tid){
        var d = $(temp).dialog({
            title: title,
            buttons: {
                "确定": function(){
                    $(this).unbind("click").find("span").text("正在处理...");
                    dealMethodCallback(type, tid);
                },
                "取消": function(){
                    d.data("widget_dialog").destroy();
                }
            },
            width: 400
        });
        $("input[name='txtstatus']").change(function(){
            var val = $(this).val();
            if (val == 1) {
                $(".money-div").find("input").removeAttr("disabled");
            }
            else {
                $(".money-div").find("input").attr("disabled", "disabled").val(0);
            }
        });
    }
    /**
     * 准备就绪后发送请求
     * @param {Object} type
     * @param {Object} tId
     */
    var dealMethodCallback = function(type, tId){
        //获取选中的值
        var value = $(".inp-area :checked").val();
        isSend = $(".inp-sendMsg :checked").val(), silver = $(".inp-money input").val(), mark = $(".inp-textarea textarea").val(), currHost = "http://" + location.host;
        url = currHost + "/" + QZ.gId + "/post/" + type;
        $.post(url, {
            checkboxs: tId,
            status: value,
            is_send: isSend,
            silver: silver,
            mark: mark
        }, function(result){
            if (result.status == 1) {
                //need fix
               // Util.MsgBox.Show({
                 //   text: result.msg,
                  //  mType: 'suc'
              //  });
                Y.successTip(result.msg)
                if (type == "del") {
                    window.location.href = currHost + "/" + QZ.gId + "/all";
                }
                else {
                    location.reload();
                }
            }
            else {
                //Util.MsgBox.Show({
                //    text: result.msg,
                //    mType: 'err'
              //  });
                Y.alertTip(result.msg);
            }
        }, "json")
    }
    //处理举报
    $("a[rel='report']").click(function(){
        if (!checkLogin()) {
            return;
        }
        var type = $(this).attr("data-type");
        QZ.dealReportAjaxMethod(type, $(this));
        return false;
    });
    //举报
    QZ.dealReportAjaxMethod = function(type, el){
        if (!QZ.dealReportTempAjax) {
            $.get(QZ.dealReportTempUrl, function(result){
                QZ.dealReportTempAjax = true;
                QZ.dealReportTemp = result;
                QZ.dealReport(type, el);
            })
        }
        else {
            QZ.dealReport(type, el);
        }
    }
    //处理举报方法
    QZ.dealReport = function(type, el){
        var username;
        if (type == "floor") {
            username = $.trim(el.parents(".topic-content-list").find(".post-ajax-tip").text());
        }
        else {
            username = $("#userName").val()
        }
        var time = QZ.getTime(), temp = Y.util.formatString(QZ.dealReportTemp, {
            userName: username,
            time: time
        }), url = window.location.href;
        var d = $(temp).dialog({
            title: "举报",
            buttons: {
                '确定': function(){
                    var sendData = {
                        gid: $("#pGId").val(),
                        pid: $("#pId").val(),
                        "submit_url": url,
                        "report_text": $(".report-pop").find("[name = 'report_text']").val(),
                        "report_type": $(".report-pop").find("[name = 'report_type']:checked").val()
                    }
                    $.post("/ajax_request/report", sendData, function(r){
                        //need fix
                        var type = r.status == 1 ? Y.successTip :Y.alertTip;
                     //   Util.MsgBox.Show({
                       //     text: r.msg,
                        //    mType: type
                       // });
                        type(r.msg);
                        
                        d.data("widget_dialog").destroy();
                    }, "json");
                },
                '取消': function(){
                    d.data("widget_dialog").destroy();
                }
            },
            width: 400
        });
    }
    //处理 帖子删除
    /* $("a[rel='del']").click(function(){
     $.confirm("确定要删除该帖子吗?", {
     width: 400
     }, function(){
     dealMethodCallback("del");
     });
     return false;
     }); */
    //处理 楼层删除
    QZ.loadingBasePath = "/static/resources/post_index/"
    $("a[rel='del_reply']").click(function(){
        var id = this.id;
        QZ.getHtml("circle_top", function(r){
            var temp = Y.util.formatString(r, {
                key: "扣除元宝",
                skey: "不扣元宝"
            });
            var delDialog = $(temp).dialog({
                title: "删除话题回复",
                width: 400,
                buttons: {
                    "确定": function(){
                        var data = $("#s_form").serializeArray();
                        data.push({
                            name: "pid",
                            value: id
                        });
                        $.ajax({
                            type: "post",
                            url: QZ.baseUrl + "/" + QZ.tId + "/del",
                            data: data,
                            dataType: "json",
                            success: function(d){
                                if (d.status == 1) {
                                  // Util.MsgBox.Show({
                                      //  text: d.msg,
                                     //  mType: 'suc'
                                   // });
                                    Y.successTip(d.msg)
                                    window.location.reload();
                                }
                                else {
                                    //Util.MsgBox.Show({
                                     //   text: d.msg,
                                     //   mType: 'err'
                                   // });
                                    Y.alertTip(d.msg)
                                }
                            }
                        });
                    },
                    "取消": function(){
                        delDialog.data("widget_dialog").destroy();
                    }
                }
            });
        })
        return false;
    });
    //处理 楼层编缉
    /*$("a[rel='edit_reply']").click(function(){
     var id = this.id;
     QZ.editReplyArea = $(this).parents(".topic-content-list");
     QZ.editReplyDialog = $.iframe({
     url: QZ.baseUrl + "/" + QZ.tId + "/edit?pid=" + id,
     width: 500,
     height: 283,
     title: "编辑回复话题",
     onclose: function(){
     QZ.floorDiv.hide();
     }
     });
     QZ.createFloor();
     //$(window).resize(QZ.createFloor);
     return false;
     });*/
    QZ.createFloor = function(){
        if ($(".topic-content-editing")[0]) {
            QZ.floorDiv = $(".topic-content-editing").show()
        }
        else {
            QZ.floorDiv = $("<div/>").addClass("topic-content-editing topic-content-list");
            QZ.floorDiv.css({
                "position": "absolute",
                "border": "1px dashed #ff0000"
            });
            $(document.body).append(QZ.floorDiv);
        }
        QZ.floorDiv.css({
            width: QZ.editReplyArea.width(),
            height: QZ.editReplyArea.height() - 1,
            left: QZ.editReplyArea.offset().left - 1,
            top: QZ.editReplyArea.offset().top - 1
        })
    }
    //监听编辑事件
    QZ.editKeyup = function(){
        //实时显示
    }
    //楼层处理回调
    QZ.editCancelAPI = function(){
        QZ.editReplyDialog.destroy();
        QZ.floorDiv.hide();
    }
    QZ.editSureAPI = function(val, id){
        Y.log(val.length)
        if (val.length < 2) {
          //  Util.MsgBox.Show({
               // text: "什么都不说，你好意思吗？",
           //   //  mType: 'err'
           // });
            Y.alertTip("什么都不说，你好意思吗？")
        }
        else 
            if (val.length > 2 && $.trim(val).length < 2) {
               // Util.MsgBox.Show({
                //    text: "只输入空格可是不好的行为！",
                 //   mType: 'err'
               // });
                Y.alertTip("只输入空格可是不好的行为！")
            }
            else {
                $.post(QZ.baseUrl + "/" + QZ.tId + "/edit?op=saveedit", {
                    gid: QZ.gId,
                    pid: id,
                    tid: QZ.tId,
                    content: val
                }, function(r){
                    if (r.state) {
                        //Util.MsgBox.Show({
                       //     text: r.data.message,
                        //    mType: 'suc'
                       // });
                        Y.successTip(r.data.message)
                        Y.log(r.data.json.content)
                        QZ.editReplyArea.find(".topic-content-text").html(r.data.json.content);
                    }
                    else {
                     //   Util.MsgBox.Show({
                          //  text: r.data.message,
                          //  mType: 'err'
                      //  });
                        Y.alertTip(r.data.message)
                    }
                    QZ.editReplyDialog.destroy();
                    QZ.floorDiv.animate({
                        height: QZ.editReplyArea.height()
                    }, 500, function(){
                        QZ.floorDiv.fadeOut();
                    })
                }, "json");
            }
    }
    //处理打赏
    $("#js_reward_btn").click(function(){
        if (!checkLogin()) {
            return;
        }
        if (!QZ.dealMoneyTempAjax) {
            $.get(QZ.dealTempMoneyUrl, function(r){
                QZ.dealMoneyTemp = r;
                QZ.dealMoneyTempAjax = true;
                QZ.dealMoneyMethod("reward");
            });
        }
        else {
            QZ.dealMoneyMethod("reward");
        }
        return false;
    });
    //处理收藏
    $("#js_fav_btn").click(function(){
        if (!checkLogin()) {
            return;
        }
        var self = $(this);
        if (self.hasClass('disabled')) {
            return;
        }
        var delid = self.attr('rel');
        var param = {
            gid: QZ.gId,
            tid: QZ.tId,
            delid: delid
        };
        self.addClass('disabled');
        $.post("/ajax_request/topics?op=collect", param, function(d){
            if (d.status) {
                //Util.MsgBox.Show({
                //    text: d.msg,
                //    mType: "suc"
               // });
                Y.successTip(d.msg)
                var label = self.find('label');
                var tag = self.find('i');
                if (delid) {
                    self.removeAttr('rel');
                    label.html(parseInt(label.html()) - 1);
                    tag.html('收藏');
                }
                else {
                    self.attr('rel', d.data);
                    label.html(parseInt(label.html()) + 1);
                    tag.html('取消' + tag.html());
                }
            }
            else {
                //Util.MsgBox.Show({
                    //text: d.msg,
                   // mType: "err"
              //  })
                Y.alertTip(d.msg)
            }
            self.removeClass('disabled');
        }, "json");
        return false;
    });
    QZ.dealMoneyMethod = function(type){
        var topicAjaxState=true;
        var dialog = $(QZ.dealMoneyTemp).dialog({
            title: "打赏",
            buttons: {
                "确定": function(){
                    var reBox = $("#js_pop_reward"), amount = reBox.find("[name = 'amount']").val(), description = reBox.find("[name = 'description']").val();
                    if(!topicAjaxState){
                        return;
                    }
                    $.post("/ajax_request/topics?op=" + type, {
                        gid: QZ.gId,
                        tid: QZ.tId,
                        amount: amount,
                        description: description
                    }, function(d){
                        topicAjaxState=true;
                        if (d.status == 1) {
                            var len = d.data.data.length;
                            if (len > 0) {
                                var html = '';
                                for (var i = 0; i < len; i++) {
                                    html += '<li><a href="/home/' + d.data.data[i].uid + '" class="record-name">' + d.data.data[i].user_name + '</a><span class="record-money orange"><b class="circle-icon circle-money"></b>+' + d.data.data[i].amount + '</span><i class="date gray fl ml10">' + d.data.data[i].atime + '</i><label class="record-label gray fl ml10">' + d.data.data[i].description + '</label></li>';
                                }
                                $("#js_reward_list").html(html);
                                $("#js_reward_all").html(d.data.total);
                                $("#js_reward_box").removeClass("y-hidden");
                            }
                            dialog.data("widget_dialog").destroy();
                        }
                        else {
                            //Util.MsgBox.Show({
                          //      text: d.msg,
                          //      mType: 'err'
                          //  });
                            Y.alertTip(d.msg)
                        }
                    }, "json");
                    topicAjaxState=false;
                },
                "取消": function(){
                    dialog.data("widget_dialog").destroy();
                }
            },
            width: 400
        });
    }
    //查看所有打赏记录
    $("#viewAll").click(function(){
        var self = $(this), isAjax = false;
        if (self.data("hasViewAll")) {
            $("#js_reward_list").animate({
                height: 110
            }, "200");
            self.text("查看所有打赏记录").data("hasViewAll", false);
        }
        else {
            if (!isAjax) {
                $.post("/ajax_request/topics?op=reward_all", {
                    gid: QZ.gId,
                    tid: QZ.tId
                }, function(d){
                    if (d.status == 1) {
                        var len = d.data.data.length;
                        if (len > 0) {
                            var html = '';
                            for (var i = 0; i < len; i++) {
                                html += '<li><a href="/home/' + d.data.data[i].uid + '" class="record-name">' + d.data.data[i].user_name + '</a><span class="record-money orange"><b class="circle-icon circle-money"></b>+' + d.data.data[i].amount + '</span><i class="date gray fl ml10">' + d.data.data[i].atime + '</i><label class="record-label gray fl ml10">' + d.data.data[i].description + '</label></li>';
                            }
                            $("#js_reward_list").html(html);
                            $("#js_reward_all").html(d.data.total);
                            $("#js_reward_box").removeClass("y-hidden");
                        }
                    }
                }, "json")
            }
            $("#js_reward_list").css({
                height: "auto"
            });
            self.text("收起打赏记录").data("hasViewAll", true);
            isAjax = true;
        }
        return false;
    });
    //购买事件绑定
    $("a[rel='buy-circle']").click(function(){
        if(!QZ.checkResourceState){
             QZ.checkResource("buy");
        }
        return false;
    });
    QZ.dealBuyMethod = function(){
        var temp = "确定支付<b class='orange'>" + QZ.buyMoney + "</b>元宝购买该资源吗？"
        $.confirm(temp, {
            title: "确认支付",
            width: 400
        },QZ.dealBuyMethodAjax);
    }
    QZ.dealBuyMethodAjax=function(){
        $.post("/ajax_request/topics?op=buy&_t=" + (new Date).getTime(), {
                gid: QZ.gId,
                tid: QZ.tId
            }, function(d){
                if (d.status == 1) {
                   /* Util.MsgBox.Show({
                        text: d.msg,
                        mType: 'suc'
                    });*/
                    Y.successTip(d.msg)
                    var u = window.location.href;
                    var flag = (u.indexOf("?") > 0 ? "&" : "?");
                    window.location.href = (u.indexOf("_t") > 0 ? u.substring(0, u.indexOf("_t")) : u) + flag + "_t=" + Date.parse(new Date()) + "#resource";
                }
                else {
                    //Util.MsgBox.Show({
                    //    text: d.msg,
                    //    mType: 'err'
                   // });
                    Y.alertTip(d.msg)
                }
            }, "json");
    }
    //管理资源列表    
    if ($("#js_resource_all").get(0)) {
        $("#js_resource_all").click(function(){
            var item = $("#js_resource_man").find("td input"), chk = false;
            var chk = $(this).attr("checked");
            item.attr("checked", chk);
        });
    }
    //转存到网盘,网盘接口
    $("#js_to_u").click(function(){
        if( !checkLogin()){
            return;
        }
         if(!QZ.checkResourceState){
             QZ.dealToDriverMethod();
        }
        return false;
    });
    /**
     * @param {Object} type | "save" ||"buy"
     */
    QZ.checkResource = function(type){
        QZ.checkResourceState=true;
        QZ.tempId=QZ.tempId||[];
        $.post("/ajax_request/topics?op=check&_t=" + (new Date).getTime(), {
            gid: QZ.gId,
            tid: QZ.tId,
            ids: QZ.tempId.join(",")
        }, function(d){
            QZ.checkResourceState=false;
            var textShow = type == "buy" ? "购买" : "转存"
            if (d.status) {
                QZ.dealResource(type);
            }
            else {
                var data = d.data, text
                result = "<div class='js_pop_file_save'><h2 class='check-tip'>系统检测到部分资源状态不正常，你确定要继续" + textShow + "吗？</h2><div class='save-file'><dl><dt><span class='f-name'>文件名</span><span class='f-state'>状态</span></dt>";
                if(!data){
                  //   Util.MsgBox.Show({
                    //    text: d.msg,
                     //   mType: 'err'
                   // });
                    Y.alertTip(d.msg);
                    return;
                }
                for (var i = 0; i < data.length; i++) {
                    var o = data[i];
                    if (data[i].state != 0) {
                        result += "<dd>";
                        result += "<span class='f-name' title='" + o.filename + "'>" + o.filename + "</span>";
                        text = data[i].state == 1 ? "被删除" : "被禁止";
                        result += "<span class='f-state'><i class='err'>" + text + "</i></span></dd>";
                    }
                    $("input[file_id='"+o.fileid+"']").removeAttr("checked");
                    try{QZ.ids = QZ.ids.replace("&id[]=" +o.fileid+"_"+$("input[file_id='"+o.fileid+"']").val(),"");}catch(e){}
                }
                
                result += "</dl></div></div>";
                QZ.js2uShowDialog = $(result).dialog({
                    "title": textShow + "提示",
                    "buttons": {
                        "确定": function(){
                            QZ.js2uShowDialog.data("widget_dialog").destroy();
                            if(type=="buy"){
                                QZ.dealBuyMethodAjax();
                            }else{
                                 QZ.dealResource(type)
                            }
                        },
                        "取消": function(){
                            QZ.js2uShowDialog.data("widget_dialog").destroy();
                        }
                    }
                });
            }
        }, "json");
    }
    QZ.dealResource = function(type){
        if (type == "save") {
            QZ.js2uIframe = $.iframe({
                url: QZ.URL_OS + "api/os/dir.php",
                width: 383,
                height: 401,
                title: "转存资源"
            });
        }
        else {
            //购买资源跳转
            QZ.dealBuyMethod();
        }
    }
    QZ.dealToDriverMethod = function(){
        var item = $("#js_resource_man").find("input:checkbox");
        QZ.ids = "";QZ.tempId=[];
        for (var i = 0, n = 0; i < item.length; i++) {
            if (item[i].checked == true && isNaN(parseFloat(item[i].value)) == false) {
                QZ.ids += "&id[]=" + $(item[i]).attr("file_id") + "_" + parseFloat(item[i].value);
                QZ.tempId.push($(item[i]).attr("file_id") + "_" + parseFloat(item[i].value));
                n++;
            }
        }
        if (QZ.ids == "") {
            //Util.MsgBox.Show({
               // text: "请选择要转存的文件",
               // mType: "err"
          //  });
            Y.alertTip("请选择要转存的文件!");
            return;
        }
        QZ.checkResource("save");
    }
    //转存到网盘的回调
    window.API_ChangDir_Cancel = function(){
        QZ.js2uIframe.destroy();
    }
    
    var Is_Post_Data = false;
    window.API_ChangDir_Confirm = function(aid, cid){
        //TODO: 
        if (aid.length == 0 || cid.length == 0) {
           // Util.MsgBox.Show({
             //   text: "请选择目录！",
               // mType: 'show'
           // });
            Y.infoTip("请选择目录!");
            return false;
        }
        if(!Is_Post_Data){
        Is_Post_Data = true;
        $.ajax({
            type: "POST",
            url: "/ajax_request/attachments?op=getfile",
            data: "user_id=" + QZ.ownerId + "&tid=" + QZ.tId + "&gid=" + QZ.gId + QZ.ids + "&aid=" + aid + "&cid=" + cid,
            dataType: "json",
            success: function(d){
                Is_Post_Data = false;
                if (d.state) {
                    var len = d.data.length;
                    var result = "<div class='save-file'><dl><dt><span class='f-name'>文件名</span><span class='f-state'>操作结果</span></dt>";
                    for (var i = 0; i < len; i++) {
                        var o = d.data[i];
                        if (i % 2 == 0) {
                            result += "<dd class='even'>";
                        }
                        else {
                            result += "<dd>";
                        }
                        result += "<span class='f-name' title='" + o.file_name + "'>" + o.file_name + "</span>";
                        if (o.state) {
                            result += "<span class='f-state'><i class='suc'>成功</i></span></dd>";
                        }
                        else {
                            
                            
                            result += "<span class='f-state'><i class='err'>" + o.msg + "</i></span></dd>";
                        }
                    }
                    result += "</dl> <div class='save-foot'><a href='javascript:;' class='btn'  onclick=\" QZ.js2uIframe.destroy();;\">关闭</a> <a  class='btn'  href='"+URL_OS+"?ac=goto_dir&aid="+aid+"&cid="+cid+"' style='margin-right:10px;' target=\"_blank\">打开目录</a></div></div>";
                    //Show_moveBox(result);
                    QZ.js2uIframe.setContent("<div id='js_pop_file_save'" + result + "</div>");
                    var item = $("#js_resource_man").find(".res-chk input");
                    for (var i = 0; i < item.length; i++) {
                        item[i].checked = false;
                    }
                }
                else {
                    //Util.MsgBox.Show({
                      //  text: d.msg,
                       // mType: 'show'
                    //});
                    Y.infoTip(d.msg);
                }
            }
        });
        }
    }
    //初始化编辑器
    window["EditorControl"] && EditorControl.Set("");
    //赞
    QZ.praiseAjax = false;
    $("#js_praise_btn").click(function(){
        if (!checkLogin()) {
            return;
        }
        var self = $(this);
        if (self.hasClass("circle-btn-disabled")) {
            return;
        }
        if (!QZ.praiseAjax) {
            QZ.praiseAjax = true;
            $.get(QZ.baseUrl + "/" + QZ.tId + "/vote?op=1&_t=" + (new Date()).getTime(), function(r){
                QZ.pariseAndMethod(self, r, "+");
            }, "json");
            /*$(".once-fun").addClass("circle-btn-disabled");*/
        }
        return false;
    });
    //踩
    $("#js_trample_btn").click(function(){
        if (!checkLogin()) {
            return;
        }
        var self = $(this);
        if (self.hasClass("circle-btn-disabled")) {
            return;
        }
        if (!QZ.trampleAjax) {
            QZ.trampleAjax = true;
            $.get(QZ.baseUrl + "/" + QZ.tId + "/vote?op=2&_t=" + (new Date()).getTime(), function(r){
                QZ.pariseAndMethod(self, r, "-");
            }, "json");
            /*$(".once-fun").addClass("circle-btn-disabled");*/
        }
        return false;
    });
    /**
     * 构建一个上升的加分效果并显示
     * @param {Object} self 触发事件的元素
     * @param {Object} r 返回的JSON对象
     * @param {Object} type 显示-或者+
     */
    QZ.pariseAndMethod = function(self, r, type){
        if (r.state) {
            self.find("label").text(r.data.json);
            var span = buildSpan(self, type);
            span.show().animate({
                top: '-=30'
            }, 500).animate({
                top: '-=20',
                opacity: 0.3
            }, 600, function(){
                span.hide();
            })
        }
        else {
          //  Util.MsgBox.Show({
             //   text: r.data.message,
              //  mType: 'err'
            //});
            Y.alertTip(r.data.message);
        }
    }
    //构建显示的span
    var buildSpan = function(e, type){
        var span, offset = e.offset();
        if ($(".show-span")[0]) {
            span = $(".show-span");
        }
        else {
            span = $("<span/>").addClass("show-span").text(type + "1");
            $(document.body).append(span);
        }
        span.css({
            left: offset.left + 30,
            top: offset.top - 15
        }).hide();
        return span;
    }
    //收藏的接口 need fix
    $("a[rel='collect']").click(function(){
        if (!checkLogin()) {
            return;
        }
        $.post("/ajax_request/topic?op=collect", {
            gid: QZ.gId,
            tid: QZ.tId
        }, function(r){
            if (r.status) {
                ////Util.MsgBox.Show({
                    //text: r.msg,
                    //mType: 'suc'
                //});
                Y.successTip(r.msg);
            }
            else {
              //  Util.MsgBox.Show({
                 //   text: r.msg,
                   // mType: 'err'
               // });
                Y.alertTip(r.msg);
            }
        }, "json")
        return false;
    });
    //评论的接口
    
    //发表回复,以下拷贝自原圈子代码--need fix
    var submitReply = function(){
        $("#js_submit_reply").hide();
        $("#js_submit_reply_enable").css("display", "inline-block");
        var tid = QZ.tId;
        var gid = QZ.gId;
        var data = {
            tid: tid,
            gid: gid,
            content: $("#js_ttHtmlEditor").val()
        };
        Y.loadTip("正在发表回复...")
        $.ajax({
            type: "POST",
            url: $("#js_post_reply_form").attr("action"),
            data: data,
            dataType: "json",
            success: function(d){
                if (d.status == 1) {
                  //  Util.Config.Result_HideTime = 30000;
                   // Util.MsgBox.Show({
                   //     text: d.msg,
                   //     mType: 'suc'
                   // });
		     EditorControl.SetContent(" ")
                    //location.href = "/" + gid + "/" + tid;
                    //location.hash = "#post_success";
                    Y.successTip(d.msg);
                    window.setTimeout(function(){
                        //location.reload();
                        $('<div class="topic-content-list" style="display: none;"><p class="topic-content-info"><a title="点击直达该位置" class="topic-operation fr" href="'+d.data.floor_url+'">本回应位于 '+d.data.floor+'</a><a href="'+URL+'/home/'+d.data.author_uid+'" rel="'+d.data.author_uid+'" class="post-ajax-tip check-login">'+d.data.author_username+'</a><label class="gray">刚刚发表回应</label></p><div class="topic-content-text">'+d.data.content+'</div></div>').appendTo($('#my_reply')).show(200);
                    }, 1000);
                }
                else {
                  //  Util.MsgBox.Show({
                    //    text: d.msg,
                    //    mType: 'err'
                   // });
                    Y.alertTip(d.msg,null,3000);
                    //Y.loadTip(d.msg)
                }
            }
        });
        
        $("#js_submit_reply").show();
        $("#js_submit_reply_enable").hide();
    }

    $(document).ready(function(){
        var hash = location.hash;
        if(hash.indexOf("post_success") != -1){
            $(document).scrollTop($(document).height());
            //location.hash = hash.replace("#post_success", "");
        }
    });

    if ($("#js_post_reply_form").get(0)) {
        $("#js_submit_reply").click(function(){
            submitReply();
            return false;
        });
        /*$("#js_post_reply_form").bind("keydown", function(e){
         if (e.ctrlKey && e.keyCode == 13) {
         submitReply();
         return false;
         }
         });*/
    }
    //编辑器中已调用
    window.SaveTextCallback = function(){
        submitReply();
        return false;
    }
    //让输入框获取焦点
    $("#circle_reply").click(function(){
        try {
            $("#js_post_reply").focus();
            $('body,html').animate({
                scrollTop: $("#js_post_reply_form").offset().top
            }, 500);
        } 
        catch (e) {
        }
        if (!Y.util.isIE6) {
            window["EditorControl"] && EditorControl.Focus();
        }
        //return false;
    });
    //评论功能
    QZ.replyTemp = $(".topic-content-input"), QZ.replyListTemp = $(".topic-content-list-hidden");
    $(".topic-content-list").delegate(".circle-comments", "click", function(){
        if (!checkLogin()) {
            return;
        }
        var self = $(this), parent = self.parents(".topic-content-list").find(".topic-content-reply-place");
        parent.append(QZ.replyTemp);
        QZ.replyTemp.show();
        QZ.replyTemp.find("textarea").val("").focus().autoTextarea({
            maxHeight: 200
        });
        
        QZ.replyTemp.find(".red").hide();
    });
    //悬浮显示回复按钮
    $(".topic-content-list").delegate("li", "mouseenter", function(){
        if ($("#is_locked").val() == 0) {
            $(this).find(".circle-comments").fadeIn("fast");
        }else{
            $(this).find(".del-comments").text("删除");
        }
        if ($("#is_del_comment").val()) {
            $(this).find(".del-comments").fadeIn("fast");
        }
    });
    $(".topic-content-list").delegate("li", "mouseleave", function(){
        $(this).find(".circle-comments").fadeOut("fast");
        $(this).find(".del-comments").fadeOut("fast");
    });
    //展开评论
    $(".topic-content-list").delegate(".show-comments", "click", function(){
        var rel = $(this).attr("rel"), length = $(this).attr("data-count");
        //收起
        if (rel == 1) {
            $(this).parent().find("li.y-hidden").hide();
            $(this).find("a").text("展开全部" + length + "条评论");
            $(this).attr("rel", 0);
        }
        else {
            $(this).parent().find("li.y-hidden").show();
            $(this).find("a").text("收起全部" + length + "条评论");
            $(this).attr("rel", 1);
        }
    });
    //删除评论
    $(".topic-content-list").delegate(".del-comments", "click", function(){
        var self = this, parent = $(this).parents(".topic-content-list"), id = $(this).parents(".topic-content-list").attr("data-id");
        $.confirm("确定删除该条评论吗？", function(){
            $.post(QZ.baseUrl + "/" + QZ.tId + "/comment?op=del&_t=" + (new Date()).getTime(), {
                pid: id,
                comid: self.rel
            }, function(r){
                if (r.state) {
                   // Util.MsgBox.Show({
                     //   text: "删除成功！",
                       // mType: 'suc'
                    //});
                    QZ.dealCommentMethod(parent, r, r.data.json);
                }
                else {
                    //Util.MsgBox.Show({
                      //  text: r.data.message,
                       // mType: 'err'
                    //});
                    Y.errorTip(r.data.message)
                }
            }, "json")
        })
    });
    //举报评论
    $(".topic-content-list").delegate(".report-comments", "click", function(){
        QZ.dealReportAjaxMethod();
    });
    //取消评论
    $("#js_cancel").click(function(){
        QZ.replyTemp.hide();
    });
    //发布评论
	QZ.jsIsClick=true;
    $("#js_comments").click(function(){
        var val = $(".ui-comments").val(), parent = $(this).parents(".topic-content-list"), id = $(this).parents(".topic-content-list").find(".circle-comments").attr("id");
        if (val.length < 3) {
            QZ.replyTemp.find(".red").show();
        }
        else {
			if(QZ.jsIsClick){
				val = QZ.formatUrl(val);
                QZ.getCommentsById(id, val, parent);
			}
        }
		QZ.jsIsClick=false;
		return false;
    });
    /**
     * 通过Id取得评论的数据
     * @param {Object} id
     * @param {Object} val
     */
    QZ.getCommentsById = function(id, val, parent){
		
        $.post(QZ.baseUrl + "/" + QZ.tId + "/comment?_t=" + (new Date()).getTime(), {
            id: id,
            comment: val
        }, function(r){
            if (r.state) {
                QZ.dealCommentMethod(parent, r, r.data.json);
            }
            else {
                //Util.MsgBox.Show({
                  //  text: r.data.message,
                  //  mType: 'err'
                //});
                Y.alertTip(r.data.message)
            }
			QZ.jsIsClick=true;
        }, "json");
    }
    /**
     * 解析获取的数据
     * @param {Object} el 放置回复的位置
     * @param {Object} r 返回的json对象
     * @param {Object} json 需要遍历的数组
     */
    QZ.dealCommentMethod = function(el, r, json){
        var ul, contentList = el.find(".topic-content-list-hidden"), hasComments = contentList[0];
        if (hasComments) {
            ul = contentList.find("ul");
        }
        else {
            var list = QZ.replyListTemp.clone();
            el.find(".topic-content-reply-place").append(list);
            list.css("display", "block");
            ul = list.find("ul");
        }
        ul.empty();
        //遍历数据
        $.each(json, function(i, item){
            var tempLi = '<li><p class="topic-content-info"><span class="fr"><a class="del-comments y-hidden" rel="' + item.comid + '">删除 | </a><a class="circle-comments y-hidden">参与评论</a></span><a href="/home/' + item.author_uid + '">' + item.author_username + '</a><label>' + item.post_time + '</label>&nbsp;&nbsp;&nbsp;&nbsp;</p><p class="reply-comment-text">' + item.comment + '</p></li>';
            if (i < json.length - 5) {
                tempLi = '<li class="y-hidden"><p class="topic-content-info"><span class="fr"><a class=" del-comments y-hidden" rel="' + item.comid + '">删除 | </a><a class="circle-comments y-hidden">参与评论</a></span><a href="/home/' + item.author_uid + '">' + item.author_username + '</a><label>' + item.post_time + '</label>&nbsp;&nbsp;&nbsp;&nbsp;</p><p class="reply-comment-text">' + item.comment + '</p></li>';
            }
            ul.append(tempLi).find('.reply-comment-text a').click(function(){
                return safeClick(this);
            });
        });
        if (json.length > 5) {
            ul.append('<li class="tc show-comments" data-count=' + json.length + '><a>展开全部' + json.length + '条评论 &nbsp;</a></li>')
        }
        QZ.replyTemp.hide();
    }
    //离开焦点,键盘输入
   /* $(".ui-comments").bind("keyup","ctrl+return",function(e){
        if ($(this).val().length < 3) {
            QZ.replyTemp.find(".red").show();
        }
        else {
            QZ.replyTemp.find(".red").hide();
            //if (e.ctrlKey && e.keyCode == 13) {
                $('#js_comments').click();
                return false;
            //}
        }
    }); */
    Y.keydown(".ui-comments","ctrl+return",function(el){
        if (el.val().length < 3) {
            QZ.replyTemp.find(".red").show();
        }
        else {
            QZ.replyTemp.find(".red").hide();
            $('#js_comments').click();
        }
    })
    //取得所有数据
    QZ.getComments = function(){
        if($("#pIds").val().length<1){
            return;
        }
        $.post(QZ.baseUrl + "/" + QZ.tId + "/comment?op=all&_t=" + (new Date()).getTime(), {
            pids: $("#pIds").val()
        }, function(r){
            if (r.state) {
                var json = r.data.json;
                $.each(json, function(key, value){
                    QZ.dealCommentMethod($("[rel=t_" + key + "]"), r, value);
                });
            }
        }, "json");
    }
    //执行
    if ($("#urlOs").val()) {
        QZ.getComments();
    }
    //ajax 提示
    Y.toolTip().show("a.post-ajax-tip", "/ajax_request/get_userinfo");
    
    //取消精华隐藏奖励元宝
    //复制楼层
    $(".copy_addr").click(function(){
        //var href = QZ.baseUrl + "/" + QZ.tId + "#" + this.id;
        var title = $(".topic-content-top h2 label").text(), href = window.location.href.split('#')[0] + "#" + this.id + "\r\n" + title;
        QZ.copy.init(href);
    });
    $(".copy_current").click(function(){
        //var href = QZ.baseUrl + "/" + QZ.tId + "#" + this.id;
        var title = $(".topic-content-top h2 label").text(), href = window.location.href;
        QZ.copy.init(href + "#\r\n" + title);
    });
    
    $(".topic-content-list").hover(function(){
        $(this).find(".hover-show").show();
    }, function(){
        $(this).find(".hover-show").hide();
    });
    
    //url访问处理
    $('.topic-content-text a').click(function(e){
        //e.preventDefault();
        return safeClick(this);
    });
    
    //安全访问url
    var safeClick = function(el){
        var reg = /^https?:\/\/([\w\.]{1,20}\.)?115.com/ig;
        var url = $(el).attr('href');
        if(url && url.match(/^https?/ig) && !url.match(reg)){
            QZ.tip=new Y.CustomTip({
                node:el,
                html:"您正试图访问一个外部链接，可能存在风险，是否继续？<br/><div style='text-align:right'><a href='"+url+"' target=\"_blank\" class='text-a'>继续访问</a><a href='javascript:;' class='text-a' onclick='QZ.tip.destroy();return false;'>取消</a>",
                direction:"top"
            });
            QZ.tip.show();
            return false;
        }
        return true;
    }
    
    //执行分页
    QZ.page=function(){
        if($("#totalPage")[0]){
            if($("#totalPage").val()==1){
                return false;
            }
            $("div.pageForTopic").page({
                _PageCount:$("#totalPage").val(),
                callBack:function(pageIndex,pageSize){
                    //Y.log(pageIndex);
                    var t=Y.loadTip("加载中...");
                    $.post(URL+"?ct=posts&ac=article",{
                        gid:QZ.gId,
                        tid:QZ.tId,
                        start:pageIndex-1
                    },function(r){
                        //Y.log(r)
                        if(r.state){
                            $("div.topic-content-page").html(r.data.content);
                            t.hide();
                        }else{
                            Y.alertTip(r.message);
                        }
                    },"json");
                }
            });
        }
    }
    QZ.page();
    
     Y.register("checkbox", function(el){
        var el = $(el);
        return {
            getId: function(){
                var ids = [];
                el.find(":checkbox").each(function(){
                    if ($(this).attr("checked")) {
                        ids.push(this.value);
                    }
                });
                return ids.join(",");
            }
        }
    });
    
    //高级弹层
    QZ.advance=function(flag){
        QZ.getHtml("circle_advanced",function(r){
            Y.log(1)
            var d=$(r).dialog({
                title:"高级操作",
                buttons:{
                    "确定":function(){
                        var data=$("#color_form").serializeArray(),id = Y.checkbox("#js_topic_lists").getId();
                        if(flag){
                            id=QZ.tId;
                        }
                        data.push({
                            name:"tid",
                            value:id
                        });
                        $.post(URL+"/"+QZ.gId+"/topics_manage",data,function(r){
                            d.data("widget_dialog").destroy();
                            if(r.status){
                                Y.successTip(r.msg);
                                setTimeout(function(){
                                    window.location.reload();
                                },2000)
                            }else{
                                Y.alertTip(r.msg);
                            }
                        },"json")
                    },
                    "取消":function(){
                        d.data("widget_dialog").destroy();
                    }
                },
                onload:function(){
                    //显示分类
                    var options="";
                    
                    if (QZ.topic_categorys) {
                        $.each(QZ.topic_categorys, function(i, n){
                            options += "<option value='" + n.c_id + "'>" + n.c_name + "</option>";
                        });
                        $("#topic_cate").append(options);
                    }
                    else {
                        $("#topic_cate").parents("li").hide();
                    }
                    //选中颜色
                    $(".select-color .color-div .color").click(function(){
                        $(this).addClass("color-press").siblings().removeClass("color-press");
                        $(".preview-content a").css({
                            color: $(this).attr("rel")
                        });
                        $(this).parent().find(":hidden").val($(this).attr("data-index"));
                    });
                   //选中置顶
                   $(".inp-top .radio").change(function(){
                            var val=$(".inp-top :checked").val();
                            $(".topic-preview").prev().remove();
                            if(val == 1){
                                $(".topic-preview").before("<span class='circle-icon topic-top'></span>");
                            }else if(val == 2){
                                $(".topic-preview").before("<span class='circle-icon topic-top-cate'></span>");
                            }
                   });
                   //选中精华
                    $(".inp-digest .radio").change(function(){
                        var val=$(".inp-digest :checked").val();
                            $(".topic-preview").nextAll(".topic-choice").remove();
                            if(val == 1){
                                $(".topic-preview").after("<span class='circle-icon topic-choice'></span>");
                            }
                    });
                    //选中锁定
                   $(".inp-lock .radio").change(function(){
                        var val=$(".inp-lock :checked").val();
                            $(".topic-preview").nextAll(".topic-lock").remove();
                            if(val == 1){
                                if($(".topic-preview").nextAll(".topic-choice")[0]){
                                    $(".topic-preview").nextAll(".topic-choice").after("<span class='circle-icon topic-lock'></span>");
                                }else{
                                    $(".topic-preview").after("<span class='circle-icon topic-lock'></span>");
                                }
                            }
                    });
                    //加粗
                   $(".inp-bold").change(function(){
                        var val=$(".inp-bold :checked").val();
                            $(".topic-preview").css("font-weight","normal");
                            if(val == 8){
                                $(".topic-preview").css("font-weight","bolder");
                            }
                    });
                }
            })
        });
    }
    
    //,...
    $(".circle-advance").click(function(){
        QZ.advance(true);
        return false;
    })
    dp.SyntaxHighlighter.HighlightAll('code');
})();
