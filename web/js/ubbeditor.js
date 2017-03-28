var UbbUtils = {
    enableSmile: true,
    smilePath: '/static/images/editor/smiles/',
    resolveImg: function(img) {
        var obj = $(img);
        if (obj.hasClass('smile')) return '[:' + obj.attr('rel') + ']';
        var src = obj.attr('src');
        return '[img=' + obj.attr('src') + ']';
    },
    makeImg: function($1) {
        var src = $1;
        if (this.isHash(src)) {
            src = URL+"/imgload?hash="+src;
        }
        return '<img src="' + src + '" />';
    },
    makeSmile: function($1) {
        return UbbUtils.enableSmile ? ('<img src="' + UbbUtils.smilePath + $1 + '.gif" class="smile"  rel="' + $1 + '" />') : ('[表情]');
    },
    resolveVideo: function(video) {
        var obj = $(video);
        var src = obj.attr('src');
        return '[video=' + obj.attr('src') + ']';
    },
    makeVideo: function($1) {
        var src = $1;
        return '<embed src="'+ src +'" quality="high" width="480" height="400" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" wmode="opaque"></embed>';
    },
    htmlReplace: [
        [/(\n|\r)+/ig, '', false],
        [/<style[^>]*>[\s\S]*?<\/style[^>]*>/igm, '', true],
        [/<script[^>]*>[\s\S]*?<\/script[^>]*>/igm, '', true],
        [/<noscript[^>]*>[\s\S]*?<\/noscript[^>]*>/igm, '', true],
        [/<select[^>]*>[\s\S]*?<\/select[^>]*>/igm, '', true],
        [/<object[^>]*?[\s\S]*?<\/object[^>]*>/igm, '', true],
        [/<marquee[^>]*>[\s\S]*?<\/marquee[^>]*>/igm, '', true],
        [/<!--[\s\S]*?-->/igm, '', true],
        [/on[a-zA-Z]{3,16}\s*?=\s*?(["'])[\s\S]*?\1/igm, '', true],
        [/<img[^>]*?.*?\/?>/ig, function($0) { return UbbUtils.resolveImg($0); }, true],
        [/<embed[^>]*?>([\s\S]*?)<\/embed[^>]*?>/igm, function($0) { return UbbUtils.resolveVideo($0); }, true],
        [/&lt;/ig, '<', false],
        [/&gt;/ig, '>', false],
        [/&quot;/ig, '"', false],
        [/&nbsp;/ig, ' '],
        [/&amp;/ig, '&', false]
    ],
    ubbReplace: [
        [/&/ig, '&amp;', false],
        [/</ig, '&lt;', false],
        [/>/ig, '&gt;', false],
        [/\"/ig, '&quot;', false],
        [/ /ig, '&nbsp;', false],
        [/\[:(\S+?)\]/ig, function($0, $1) { return UbbUtils.makeSmile($1); }, true],
        [/\[img=(\S*?)\]/ig, function($0, $1) { return UbbUtils.makeImg($1); }, true],
        [/\[img[^\]=]*\]([\s\S]*?)\[\/img[^\]]*\]/ig, function($0, $1) { return UbbUtils.makeImg($1); }, true],
        [/\[video=(\S*?)\]/ig, function($0, $1) { return UbbUtils.makeVideo($1); }, true],
        [/\[video[^\]=]*\]([\s\S]*?)\[\/video[^\]]*\]/ig, function($0, $1) { return UbbUtils.makeVideo($1); }, true]
    ],
    replace: function(str, rep, ign) {
        str = str + '';
        for (var i = 0; i < rep.length; ++i) {
            if (rep[i][2]){
                while (str.search(rep[i][0]) != -1){
                    str = str.replace(rep[i][0], (ign ? '' : rep[i][1]));
                }
            }
            else{
                str = str.replace(rep[i][0], (ign ? '' : rep[i][1]));
            }
        }
        return str;
    },
    html2Ubb: function(str){
        return UbbUtils.replace(str, UbbUtils.htmlReplace);
    },
    ubb2Html: function(str){
        return UbbUtils.replace(str, UbbUtils.ubbReplace);
    },
    isHash: function(str) {
        var regExp = /^[A-F0-9]{40}$/;
        if(str.match(regExp)) return true;
        else return false;
    },
    isUrl: function(str){
        var regExp = /(http[s]?|ftp):\/\/[^\/\.]+?\..+\w$/i;
        if(str.match(regExp)) return true;
        else return false;
    }      
};

var Editor = (function(){
    var temp = {
        editor : "",
        editBox : "",
        toolBar : "",
        CaretPos: 0,
        selTxt : "",
        isOn : false
    }
    //建立编辑
    var build = function(textarea){
        temp.editor = $("#"+textarea);
        temp.editor.wrap('<div class="editor-box" id="'+textarea+'_editor"><div class="editor-bc"></div></div>');
        $('<div class="editor-bh"><div class="opt"><a href="javascript:;" class="smilies">表情<i></i></a> <a href="javascript:;" class="picture">图片<i></i></a> <a href="javascript:;" class="file">文件<i></i></a> <a href="javascript:;" class="video">视频<i></i></a></div></div>').insertBefore(temp.editor.parent());
        temp.editBox = $("#"+textarea+"_editor");
        temp.toolBar = $("#"+textarea+"_editor").find(".editor-bh");
        bindEvent();
    }
    //绑定事件
    var bindEvent = function(){
        var picture = temp.toolBar.find(".picture");
        picture.unbind("click").click(function(){
            promptWindow.pictureBox(this);
        });
        var video = temp.toolBar.find(".video");
        video.unbind("click").click(function(){
            promptWindow.videoBox(this);
        });
        var smilies = temp.toolBar.find(".smilies");
        smilies.unbind("click").click(function(){
            promptWindow.smiliesBox(this);
        });
        //监听事件
        temp.editor.unbind("keyup").bind("keyup",function(){
            getCursortPosition();
            getSelectionText();
        }).bind("mouseup",function(){
            getCursortPosition();
            getSelectionText();
        });

        temp.toolBar.find(".opt a").hover(
            function(){temp.isOn = true},
            function(){temp.isOn = false}
        );

        $(document).click(function(e){
            if(!temp.isOn){
                if(temp.optBox){
                    temp.optBox.hide();
                }
            }
        });
    }


    
    //弹出窗口
    var promptWindow = {
        bindSubEvent : function(subBox,type){
            var completeInsert  = function(){
                var val = subBox.find('input').val(),
                    ubbTxt = "";
                switch(type){
                    case "picture":
                        ubbTxt = UbbUtils.html2Ubb(UbbUtils.makeImg(val));
                        break;
                    case "video":
                        ubbTxt = UbbUtils.html2Ubb(UbbUtils.makeVideo(val));
                        break;
                    case "smile":
                        var selSmile = subBox.find('a.selected img');
                        ubbTxt = UbbUtils.html2Ubb(UbbUtils.makeSmile(selSmile.attr("rel")));
                        break;
                }
                
                if(type == "smile"){
                    insertText(ubbTxt);
                    subBox.parent().hide();
                    subBox.find('input').val("");
                }else if((UbbUtils.isUrl(val) || UbbUtils.isHash(val)) && type != "smile"){
                    insertText(ubbTxt);
                    subBox.parent().hide();
                    subBox.find('input').val("");
                }
                else{
                    alert("不是有效的路径");
                }
            }            
            // 回车提交
            subBox.unbind("keydown").keydown(function(e){
                if(e.keyCode == 13){
                    completeInsert();
                    return false;
                }
            });
            //取消按钮
            subBox.find('.btn-no').unbind("click").click(function(){
                subBox.parent().hide();
                temp.editor.focus();
                return false;
            });
            //确定按钮
            subBox.find('.btn-ok').unbind("click").click(function(){
                completeInsert();
                return false;
            });
            //表情
            subBox.find('.smile-list a').unbind("click").click(function(){
                subBox.find('.smile-list a').removeClass("selected");
                $(this).addClass("selected");
                completeInsert();
                return false;
            });
            //停止冒泡
            subBox.find("input").bind("click", function(e){
                if($.browser.msie){
                    event.cancelBubble = true;
                }else{
                    e.stopPropagation();
                    e.preventDefault();
                }
            });
        },
        creatPromptState : false,
        creatPrompt : function(){
            if(!promptWindow.creatPromptState){
                temp.optBox = $('<div class="edit-opt" id="_editer_pop"></div>');
                //temp.toolBar.append(optBox);
                $("body").append(temp.optBox);
                promptWindow.creatPromptState = true;
                temp.optBox.hover(
                    function(){temp.isOn = true;},
                    function(){temp.isOn = false}
                );
            }
            temp.optBox.show();
        },
        smilePic : ['ambivalent','angry','confused','content','cool','crazy','cry','embarrassed','footinmouth','frown','gasp','grin','heart','hearteyes','innocent','kiss','laughing','minifrown','minismile','moneymouth','naughty','nerd','notamused','sarcastic','sealed','sick','slant','smile','thumbsdown','thumbsup','wink','yuck','yum'],
        smiliesBox : function(obj){
            var x = $(obj).offset().left;
            var y = $(obj).offset().top;
            promptWindow.creatPrompt();
            var smileList = '<ul class="smile-list clearfix">';
            for(var i=0;i<promptWindow.smilePic.length;i++){
                smileList += '<li><a href="javascript:;"><img src="'+UbbUtils.smilePath+promptWindow.smilePic[i]+'.png" class="smile" rel="'+promptWindow.smilePic[i]+'" /></a></li>'
            }
            smileList += "</ul>";
            temp.optBox.html('<div class="mk-pop-a smile-img"><div class="ph-a"><h3>插入表情</h3><a href="javascript:;" class="btn-close btn-no"></a><i class="arrow"></i></div><div class="pc-a">'+smileList+'</div></div>');
            var l = $(obj).offset().left;
            var t = $(obj).offset().top;
            temp.optBox.css({left:l,top:(t+32),position:"absolute"});
            promptWindow.bindSubEvent(temp.optBox.find('.smile-img'),"smile");
        },
        pictureBox : function(obj){
            promptWindow.creatPrompt();
            temp.optBox.html('<div class="mk-pop-a pic-url"><div class="ph-a"><h3>插入图片</h3><a href="javascript:;" class="btn-close btn-no"></a><i class="arrow"></i></div><div class="pc-a"><div class="inp-wrap clearfix"><input type="text" class="inp" /><a href="javascript:;" class="btn-normal btn-b btn-ok"><i>确定</i></a><p class="v-tip">插入网络图片或者115网盘下相册图片哈希码(SHA1)</p></div></div></div>');
            var l = $(obj).offset().left;
            var t = $(obj).offset().top;
            temp.optBox.css({left:l,top:(t+32),position:"absolute"});
            promptWindow.bindSubEvent(temp.optBox.find('.pic-url'),"picture");
        },
        videoBox : function(obj){
            promptWindow.creatPrompt();
            temp.optBox.html('<div class="mk-pop-a video-url"><div class="ph-a"><h3>插入视频</h3><a href="javascript:;" class="btn-close btn-no"></a><i class="arrow"></i></div><div class="pc-a"><div class="inp-wrap clearfix"><input type="text" class="inp" /><a href="javascript:;" class="btn-normal btn-b btn-ok"><i>确定</i></a><p class="v-tip">目前已支持<a href="http://www.youku.com/" target="_blank">优酷网</a>、<a href="http://www.tudou.com/" target="_blank">土豆网</a>、<a href="http://www.ku6.com/" target="_blank">酷6网</a>、<a href="http://www.56.com/" target="_blank">56网</a>网站。（<a href="/group/115" target="_blank">如何使用</a>？）</p></div></div></div>');
            var l = $(obj).offset().left;
            var t = $(obj).offset().top;
            temp.optBox.css({left:l,top:(t+32),position:"absolute"});
            promptWindow.bindSubEvent(temp.optBox.find('.video-url'),"video");
        }
    }    
    var getCursortPosition = function(){
        var start = 0;
        var textBox = temp.editor.get(0);
        temp.enter = 0;
        //Firefox
        if(typeof(textBox.selectionStart) == "number"){
            start = textBox.selectionStart;
        }
        //IE
        else if(document.selection){
            var range = document.selection.createRange();
            if(range.parentElement().id == textBox.id){
                var range_all = document.body.createTextRange();
                range_all.moveToElementText(textBox);
                for (start=0; range_all.compareEndPoints("StartToStart", range) < 0; start++){
                    range_all.moveStart('character', 1);
                }
                for (var i = 0; i <= start; i ++){
                    if (textBox.value.charAt(i) == '\n'){
                        start++;
                        temp.enter++;
                    }
                }
            }
        }
        temp.CaretPos = start;
    }
    
    //获取选中文本
    function getSelectionText(){
        var obj = temp.editor.get(0);
        //ie利用Range，这个和非文本框的是一样的!
        if(document.selection && (document.selection.type == "Text")){
            temp.selTxt = document.selection.createRange().text;
        }
        //ff、chrome，用getSelection
        else if(obj.selectionStart != undefined && obj.selectionEnd != undefined){
            var start = obj.selectionStart;
            var end = obj.selectionEnd;
            temp.selTxt = obj.value.substring(start, end);
        }
    }
    //插入文本
    var insertText = function(txt){
        var ex = temp.editor.get(0);
        var end = ex.value.length;
        //设置插入光标位置
        var setCaretPos = function(n){
            if(ex.createTextRange) {   // IE  
                var textRange = ex.createTextRange();  
                textRange.moveStart('character',n);              
                textRange.collapse();         
                textRange.select();       
            } else if(ex.setSelectionRange) { // Firefox  
                ex.setSelectionRange(n,n);  
                ex.focus();  
            }
        }
        if(temp.CaretPos >= 0 && temp.selTxt != ''){
            var selStart = temp.CaretPos,
                selLength = temp.selTxt.length;
                selTxtInArea = ex.value.substr(selStart,selLength);
            ex.value.replace("selTxtInArea",'');
            ex.value = ex.value.substr(0, selStart) + txt + ex.value.substr((selStart + selLength),end);
            setCaretPos((selStart+txt.length-temp.enter));
        }
        else if (temp.CaretPos >= 0 && temp.selTxt == ''){
            ex.value = ex.value.substr(0, temp.CaretPos) + txt + ex.value.substr(temp.CaretPos,end);
            setCaretPos((temp.CaretPos+txt.length-temp.enter));
        }
        else{
            ex.value += txt;
            ex.focus();
        }
    }

    return {
        init : function(id){
            build(id);
            UbbUtils.smilePath = URL + UbbUtils.smilePath;
        }
    }
})();

// 附件
document.domain = "115.com";
var tmp_attachments_count = '';
function API_SelectFile_Confirm(arr)
{
    var tid = $("#tid").val();
    var gid = $("#gid").val();
    var data = "&fileids=";    
    for(var i = 0, len = arr.length; i <len; i++){
        data += arr[i]["file_id"] + ",";
    }
    
    // 取最后一个fid
    data += "&last_id="+attachments_count + "&tid=" + tid + "&gid=" + gid;
    $.ajax({
        type : "POST",
        url : "/ajax_request/attachments?op=add",
        data : data,
        success : function(d){
            var d = eval("("+ d +")");
            if(d.status == 1)
            {
                set_attachments_data( d.data );
                API_SelectFile_Cancel();
            }
            else
            {
                Util.MsgBox.Show({text:d.msg,mType:"err"});
            }
        }
    });
}

function API_SelectFile_Cancel()
{
    if (QZ.js2F) {
        QZ.js2F.destroy();
    }
}

function POP_SelectFile()
{
    if(is_email_verify == "Y")
    {
        QZ.js2F = $.iframe({
            url: URL_OS + "api/os/info.php?_t=" + (new Date()).getTime(),
            width: 754,
            height: 500,
            title: "添加网盘文件"
        });
    }
    else
    {
        $.confirm("很抱歉，您还没有验证电子邮箱？<br><br>点\"确定\"马上验证。", function(){
            window.open(URL_MY+'?ct=security&ac=email','');
        });
    }
    
}

function set_attachments_data( data, flag )
{
    if( tmp_attachments_count == '' )
    {
        tmp_attachments_count = attachments_count;
    }
    if( attachments_count > 0 && flag!="sort" )
    {
        var at = "";
    }
    else
    {
        var at = "<dt><span class=\"f-id\">序号</span><span class=\"f-name\">标题</span><span class=\"f-size\">大小</span><span class=\"f-opt\">操作</span></dt>";
        tmp_attachments_count = 0;
    }

    for(var i=0, len=data.length; i<len; i++)
    {
        var o = data[i];
        tmp_attachments_count++;
        at += '<dd fid="'+o["fileorder"]+'"><span class="f-id">'+tmp_attachments_count+'</span><span class="f-name">'+o["filename"]+'</span><span class="f-size">'+o["filesize"]+'</span><span class="f-opt"><i class="up" rel="up"></i><i class="down" rel="down"></i>';
        if(flag=="" || is_have_buy=="N")at += '<i class="del" onclick="FileSort.delFile(\''+o["fileorder"]+'\');"></i>';
        at += '</span></dd>';
    }

    if(len>0)
    {
        $("#js_files_box").show();
        if( attachments_count > 0 && flag!="sort" )
        {
            $("#js_file_list").append( at );
        }
        else
        {
            $("#js_file_list").html( at );
        }
        
        if(flag!="sort")
        {
            $(".add-file").css("display","block");
        }        
    }
    else if( attachments_count > 0 && !len )
    {}
    else
    {
        $("#js_file_list").html("");
        $(".add-file").css("display","none");
        $("#js_files_box").hide();
    }
    //文件排序
    FileSort.init("js_file_list");
}

// 附件排序
var FileSort = (function(){
    var fileArr, sort_o="SORT_DESC";
    var start = function(obj){
        var filesObj = $("#"+obj);
        filesObj.find(".f-opt i").bind("click",function(){
            var p = $(this).parent().parent();
            var rel = $(this).attr("rel");
            if(rel == "down"){
                p.next("dd").after(p);
            }
            else if(rel == "up"){
                p.prev("dd").before(p);
            }
            resetList(filesObj);
        });
        resetList(filesObj);
    }
    var resetList = function(obj){
        obj.find("dd i[rel='up']").removeClass("dis-up").addClass("up");
        obj.find("dd i[rel='down']").removeClass("dis-down").addClass("down");        
        obj.find("dd:first i[rel='up']").removeClass("up").addClass("dis-up");
        obj.find("dd:last i[rel='down']").removeClass("down").addClass("dis-down");
        fileArr = [];
        obj.find("dd").each(function(){
            fileArr.push($(this).attr("fid"));
        });
    }
    return {
        init : function(obj){
            start(obj);
            
            $("#"+obj+" .f-name").attr("title","点击排序");
            $("#"+obj+" .f-name").css("cursor","pointer");
            $("#"+obj+" .f-name").unbind("click").bind("click",function(){
                FileSort.sortFile("filename");
            });
            $("#"+obj+" .f-size").attr("title","点击排序");
            $("#"+obj+" .f-size").css("cursor","pointer");
            $("#"+obj+" .f-size").unbind("click").bind("click",function(){
                FileSort.sortFile("filesize");
            });
        },
        getFileSort : function(){
            return fileArr;
        }, 
        delFile : function( id ){
            var tid = $("#tid").val();
            var gid = $("#gid").val();
            $.ajax({
                type : "POST",
                url : "/ajax_request/attachments?op=del",
                data : "id="+id+"&tid="+tid+"&gid="+gid,
                success : function(d){
                    var d = eval("("+ d +")");
                    if(d.status == 1)
                    {                    
                        //set_attachments_data( d.data );
                        $("[fid='"+id+"']").remove();
                        FileSort.init("js_file_list");
                    }
                    else
                    {
                        Util.MsgBox.Show({text:d.msg,mType:"err"});    
                    }
                }
            }); 
        },
        sortFile : function( o ){
            var tid = $("#tid").val();
            var gid = $("#gid").val();
            $.ajax({
                type : "POST",
                url : "/ajax_request/attachments?op=sort",
                data : "o="+o+"&tid="+tid+"&gid="+gid+"&sort="+sort_o,
                success : function(d){
                    var d = eval("("+ d +")");
                    if(d.status == 1)
                    {
                        sort_o = sort_o=="SORT_DESC"?"SORT_ASC":"SORT_DESC";
                        set_attachments_data( d.data, 'sort' );
                    }
                    else
                    {
                        Util.MsgBox.Show({text:d.msg,mType:"err"});    
                    }
                }
            });     
        }
    }
})();





