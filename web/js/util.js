/**
*　事符格式化
*/
String.format = function(str) {
    var args = arguments, re = new RegExp("%([1-" + args.length + "])", "g");
    return String(str).replace(
    re,
    function($1, $2) {
        return args[$2];
    }
    );
};
String.formatmodel = function(str,model){
    for(var k in model){
        var re = new RegExp("{"+k+"}","g");
        str = str.replace(re,model[k]);
    }
    return str;
}

var Util = {};

/*
* 配置信息
*/
Util.Config = {
    //遮罩层背景颜色
    Screen_Background: "#fff",
    //遮罩层透明度
    Screen_Opacity: "1",
    //遮罩层内容背景颜色
    Screen_ContentBg: "transparent",
    Screen_PositionTop:"50%",
    Screen_PositionLeft:"50%",

    //结果隐藏时间
    Result_HideTime:1000,

    TextBoxDefaultColor: "#bababa",
    TextBoxActiveColor:"#333"
}

/*
* 分类选择器(二级联动)
*/
Util.selCategory = (function(){
    var t = {}
    var start = function(obj){
        if (!obj || !obj.data) {
            alert('无法初始化数据!');
            return;
        }
        t.data = obj.data;
        t.parSel = document.getElementById((obj.parent.id || obj.parent));
        t.chiSel = document.getElementById((obj.chilren.id || obj.chilren));
        t.chiSel.style.display = 'none';
        t.defTxtParent = obj.parent.def  ? obj.parent.def : ["请选择",""];
        t.defTxtChild  = obj.chilren.def ? obj.chilren.def : null;
        t.defSign = " ";
        getParent();
        t.parSel.onchange = function(){
            getChilren(this.value);
        }
        if (obj.selected) {
            selectVal(obj.selected[0],obj.selected[1]);
        }
    }
    //获取父级
    var getParent = function(){
        var getPanOpt = function(){
            var pd = sort('sort_order',t.data);
            for(var i in pd){
                var obj = pd[i];
                t.parSel.options.add(new Option(obj.abbrev+t.defSign+obj.name,obj.cid)) ;
            }
        }
        t.parSel.options.length = 0;
        t.parSel.options.add(new Option(t.defTxtParent[0],t.defTxtParent[1]));
        getPanOpt();
    }
    //获取子级
    var getChilren = function(id){
        var getChiOpt = function(){
            if (!id) return;
            var children = t.data[id].children;
            if (t.defTxtChild){
                t.chiSel.options.add(new Option(t.defTxtChild[0], t.defTxtChild[1]));
            }
            for(var i in children){
                var obj = children[i];
                t.chiSel.options.add(new Option(obj.abbrev+t.defSign+obj.name,obj.cid));
            }
        }
        t.chiSel.options.length = 0;
        getChiOpt();
        t.chiSel.options.length == 0 ? t.chiSel.style.display = 'none' : t.chiSel.style.display = '';
    }
    //选定分类
    var selectVal = function(parID,chiID){
        t.parSel.value = parID;
        getChilren(parID);
        t.chiSel.value = chiID;
    }
    //排序
    var sort = function(field,source){
        var temp,data=[];
        for(var i in source){
            data.push(source[i]);
        }
        data.sort(function(a,b){return a.sort_order - b.sort_order});
        return data;
    }
    return {
        init : function(data){
            start(data);
        },
        sel : function(parSelID,chiSelID){
            selectVal(parSelID,chiSelID);
        }
    }
})();

/*
* 地域选择器(三级联动)
*/
Util.selArea = new function() {
    var self = this, area_data, prov_mod, city_mod;
    var txt_default = "地球村";
    
    //初始化区域数据并创建选择框
    //可以同时初始化多个select实例[config1,config2...]
    this.init = function(config, source){
        if(!source || !source.data) {
            alert('无法初始化区域数据!');
            return;
        }
        area_data = source.data;
        prov_mod  = source.prov_mod;
        city_mod  = source.city_mod;
        
        if (config.length) {
            for(var i = 0; i < config.length; i++) {
                self.build_select(config[i]);
            }
        } else {
            self.build_select(config);
        }
    }

    //获取省份列表
    this.get_provs = function( selprov ) {
        var provs = [];
        var i = 0;
        var aselprov = selprov ? selprov : 0;
        for(p in area_data) {
            if( p=='l' ) continue;
            provs[i] = {'id':p, 'name':area_data[p].n, 'sel':(aselprov==p) };
            i++;
        }
        return provs;
    };

    //获取城市列表
    this.get_citys = function( prov, selcity ) {
        var citys = [];
        var i = 0;
        if (!prov) return false;
        var aselcity = selcity ? selcity : 0;
        if( !area_data[prov] || area_data[prov].l == 0) {
            return false;
        }
        for(c in area_data[prov]['c']) {
            if( c=='l' ) continue;
            citys[i] = {'id':c, 'name':area_data[prov]['c'][c].n, 'sel':(aselcity==c) };
            i++;
        }
        return citys;
    };

    //获取县区列表
    this.get_towns = function( city, seltown ) {
        var towns = [];
        var i = 0;
        if (!city) return false;
        var aseltown = seltown ? seltown : 0;
        var prov = Math.floor( city / prov_mod ) * prov_mod;
        if( !area_data[prov] || !area_data[prov]['c'][city].l ) {
            return false;
        }
        for(t in area_data[prov]['c'][city]['t']) {
            towns[i] = {'id':t, 'name':area_data[prov]['c'][city]['t'][t], 'sel':(aseltown==t) };
            i++;
        }
        return towns;
    };

    /***************************
    参数说明
    var _config = {
    'sel_id'    : select容器id,
    'sel_class' : select容器class
    'step'      : 选择框级数(可选, 默认3),
    'name1'     : 第一级选择框名称(可选),
    'name2'     : 第二级选择框名称(可选),
    'name3'     : 第三级选择框名称(可选),
    'sel_value' : 默认选中的项目(可选)
    };
    ***************************/
    this.build_select = function(  _config ) {
        //设置默认参数
        var sel_id, sel_class, step, name1, name2, name3, sel_value, sel_prov, sel_city, sel_town, p,callback;
        if( !_config || !_config['sel_id'] ) {
            alert('必须指定地区选择框容器ID!');
            return ;
        }
        sel_id = _config['sel_id'];
        step  = !_config['step']  ? 3 : _config['step'];
        name1 = !_config['name1'] ? 'prov_id' : _config['name1'];
        name2 = !_config['name2'] ? 'city_id' : _config['name2'];
        name3 = !_config['name3'] ? 'town_id' : _config['name3'];
        sel_value  = !_config['sel_value']  ? 0 : _config['sel_value'];
        sel_class  = !_config['sel_class']  ? 'text' : _config['sel_class'];
        callbackProv= _config.callbackProv;
		callbackCity= _config.callbackCity;
		callbackTown=_config.callbackTown;
        //计算选中的值
        if( sel_value!=0 ) {
            if( sel_value % prov_mod == 0) {
                sel_prov = sel_value;
                sel_city = sel_town = 0;
            } else if( sel_value % city_mod == 0) {
                sel_prov = Math.floor( sel_value / prov_mod) * prov_mod;
                sel_city = sel_value;
                sel_town = 0;
            } else {
                sel_prov = Math.floor( sel_value / prov_mod) * prov_mod;
                sel_city = Math.floor( sel_value / city_mod) * city_mod;
                sel_town = sel_value;
            }
        } else {
            sel_prov = sel_city = sel_town = 0;
        }
        
        //省
        var html = "<select name=\""+name1+"\" id=\""+sel_id+"_prov\" class=\""+sel_class+"\" onchange=\"Util.selArea.rebuild_city('"+sel_id+"', this.options[this.selectedIndex].value,"+callbackProv+");\">\n";
        html += sel_prov==0 ? "<option value='0'>"+txt_default+"</option>\n" :
                              "<option value='0' selected='selected'>"+txt_default+"</option>\n";
        var provs = this.get_provs( sel_prov );
        for(p in provs) {
            html += provs[p]['sel'] ?  "<option value='"+provs[p]['id']+"' selected='selected'>"+provs[p]['name']+"</option>\n" :
                                       "<option value='"+provs[p]['id']+"'>"+provs[p]['name']+"</option>\n";
        }
        html += "</select>\n";
        
        //城市
        if( step > 1 ) {
            var city_display = "none";
            var city_options = sel_city==0 ? "<option value='0'>不限</option>\n" :
                                "<option value='0' selected='selected'>不限</option>\n";
            if( sel_prov > 0 ) {
                var citys = self.get_citys( sel_prov, sel_city );
                if( citys ) {
                    for(p in citys) {
                        city_options += citys[p]['sel'] ?  "<option value='"+citys[p]['id']+"' selected='selected'>"+citys[p]['name']+"</option>\n" :
                                                   "<option value='"+citys[p]['id']+"'>"+citys[p]['name']+"</option>\n";
                    }
                    city_display = "";
                }
            }
            html += "<select name=\""+name2+"\" id=\""+sel_id+"_city\" style=\"display:"+city_display+"\" class=\""+sel_class+"\" onchange=\"Util.selArea.rebuild_town('"+sel_id+"', this.options[this.selectedIndex].value,"+callbackCity+");\">\n";
            html += city_options + "</select>\n";
        }
        
        //县区
        if( step > 2 ) {
            var town_display = "none";
            var town_options = sel_town==0 ? "<option value='0'>不限</option>\n" :
                                "<option value='0' selected='selected'>不限</option>\n";
            if( sel_city > 0 ) {
                var towns = self.get_towns( sel_city, sel_town );
                if( towns ) {
                    for(p in towns) {
                        town_options += towns[p]['sel'] ?  "<option value='"+towns[p]['id']+"' selected='selected'>"+towns[p]['name']+"</option>\n" :
                                                   "<option value='"+towns[p]['id']+"'>"+towns[p]['name']+"</option>\n";
                    }
                    town_display = "";
                }
            }
            html += "<select name=\""+name3+"\" style=\"display:"+town_display+"\" class=\""+sel_class+"\" id=\""+sel_id+"_town\" onchange='"+callbackTown+"'>\n";
            html += town_options + "</select>\n";
        }
        
        document.getElementById(sel_id).innerHTML = html;
        
        return ;
        
    };

    //创建选择框选项
    this.create_options = function(oText, oValue) {
        var cOption = document.createElement('OPTION');
        cOption.text  = oText  || '不限';
        cOption.value = oValue || '0';
        return cOption;
    }

    //重新生成城市选择数据
    this.rebuild_city = function( cin_id, prov ,callback) {
		if(typeof callback=="function"){
			callback(prov);
		}
        var _city = document.getElementById( cin_id+'_city' );
        if( !_city ) return false;
        var aOption = null;
        var citys = self.get_citys( prov, 0 );
        _city.options.length = 0;
        _city.options.add(self.create_options());
        if( citys ) {
            for(p in citys) {
                 aOption = self.create_options(citys[p]['name'], citys[p]['id']);
                 _city.options.add(aOption);
            }
            _city.style.display = "";
        } else {
            _city.style.display = "none";
        }
        self.rebuild_town(cin_id, 0);
        return true;
    }

    //重新生成地区选择数据
    this.rebuild_town = function( cin_id, city ,callback) {
		if(city){
			if(typeof callback == "function"){
				callback(city);
			}
		}
        var _town = document.getElementById( cin_id+'_town' );
        if( !_town ) return false;
        var aOption = null;
        var towns = self.get_towns( city, 0 );
        _town.options.length = 0;
        _town.options.add(self.create_options());
        if( towns ) {
            for(p in towns) {
                 aOption = self.create_options(towns[p]['name'], towns[p]['id']);
                 _town.options.add(aOption);
            }
            _town.style.display = "";
        } else {
            _town.style.display = "none";
        }
        return true;
    }
}//end class

/*
* 弹出层
*/
Util.ScreenManager = {
    /*Public 隐藏方法*/
    Hide: function(doFun){
        this.canClose = true;
        this.popCoverDiv(false);
        if(doFun){
            doFun();
        }
    },
    /*Public 显示方法*/
    Show: function(containBox,isClickHide){
        if(isClickHide != undefined){
            Util.ScreenManager.IsClickHide = isClickHide;
        }
        else{
            Util.ScreenManager.IsClickHide = false;
        }
        this.popCoverDiv(true,containBox);
    },
    //取得页面的高宽
    getBodySize: function (){
        var bodySize = [];
        with(document.documentElement) {
            bodySize[0] = (scrollWidth>clientWidth)?scrollWidth:clientWidth;//如果滚动条的宽度大于页面的宽度，取得滚动条的宽度，否则取页面宽度
            bodySize[1] = (scrollHeight>clientHeight)?scrollHeight:clientHeight;//如果滚动条的高度大于页面的高度，取得滚动条的高度，否则取高度
        }
        return bodySize;
    },
    config:{
        cachebox:"screen_cache_box",/*缓存层*/
        contentbox:"screen_content_box",/*内容层*/
        coverbox:"screen_cover_div"/*透明层*/
    },
    canClose:true,
    ShowSelfControl:function(containBox,showFun){
        Util.ScreenManager.IsClickHide = true;
        this.popCoverDiv(3,containBox,undefined,showFun);
    },
    //创建遮盖层
    popCoverDiv: function (isShow,containBox,setWidth,showFun){
        var screenBox = document.getElementById(Util.ScreenManager.config.coverbox);
        if (!screenBox) {
            //如果存在遮盖层，则让其显示
            //否则创建遮盖层
            var coverDiv = document.createElement('div');
            document.body.appendChild(coverDiv);
            coverDiv.id = Util.ScreenManager.config.coverbox;
            var bodySize;
            with(coverDiv.style) {
                if ($.browser.msie && $.browser.version == 6) {
                    position = 'absolute';
                    background = Util.Config.Screen_Background;
                    left = '0px';
                    top = '0px';
                    bodySize = this.getBodySize();
                    width = '100%';
                    height = bodySize[1] + 'px';
                }
                else{
                    position = 'fixed';
                    background = Util.Config.Screen_Background;
                    left = '0';
                    top = '0';
                    width = '100%'
                    height = '100%';
                }
                zIndex = 9998;
                if (document.all) {
                    filter = "Alpha(Opacity=" + Util.Config.Screen_Opacity + "0)";	//IE逆境
                } else {
                    opacity = Number("0."+Util.Config.Screen_Opacity);
                }
                if(!isShow){
                    display = "none";
                }
            }
            coverDiv.onclick = function(){
                if(Util.ScreenManager.canClose){
                    if(Util.ScreenManager.IsClickHide == undefined || Util.ScreenManager.IsClickHide == false){
                        coverDiv.style.display = "none";
                        document.getElementById(Util.ScreenManager.config.contentbox).style.display = "none";
                    }
                }
            };

            var contentDiv = document.createElement("div");
            contentDiv.id = Util.ScreenManager.config.contentbox;
            with(contentDiv.style){
                position = "absolute";
                backgroundColor = Util.Config.Screen_ContentBg;
                zIndex = 9999;
            }
            document.body.appendChild(contentDiv);
            contentDiv.onmouseover = function(){
                Util.ScreenManager.canClose = false;
            };

            contentDiv.onmouseout = function(){
                Util.ScreenManager.canClose = true;
            };
            screenBox = contentDiv;
        }
        screenBox.style.display = isShow ? "" : "none" ;
        if(isShow == 3){
            if(showFun){
                showFun();
            }
        }
        else{
            document.getElementById(Util.ScreenManager.config.contentbox).style.display = isShow ? "" : "none" ;
            //alert(0);
            if(isShow && containBox){
                //创建Cache Box
                var cacheBox = document.getElementById(Util.ScreenManager.config.cachebox);
                if(!cacheBox){
                    var cBox = document.createElement("div");
                    document.body.appendChild(cBox);
                    cBox.id = Util.ScreenManager.config.cachebox;
                    cBox.style.display = "none";
                    cacheBox = cBox;
                }
                var cBox = document.getElementById(Util.ScreenManager.config.contentbox);
                var contentNodes = cBox.childNodes;
                for(var i = 0,len = contentNodes.length; i < len; i++){
                    cacheBox.appendChild(contentNodes[i]);
                }
                containBox.style.display = "";               
                cBox.appendChild(containBox);
                //重置层的位置
                var cBoxW = $(containBox).width();
                var cBoxH = $(containBox).height();
                var cBoxT = ($(document).scrollTop() != 0) ? $(document).scrollTop()+200+ "px" : Util.Config.Screen_PositionTop;
                var cBoxL = Util.Config.Screen_PositionLeft;
                $(cBox).css({
                    top:cBoxT,
                    left:cBoxL,
                    marginLeft:-(cBoxW/2),
                    marginTop:-(cBoxH / 2)
                });
            }
        }
        this.canClose = true;
    }
}
/*
 * 提示框
 */
Util.MsgBox = (function(){
    var _Config = {
        confirmTemp:'<div class="mk-pop-a confirm-box"><div class="ph-a"><h3>%2</h3><a href="javascript:;" class="btn-close" rel="close"></a></div><div class="pc-a"><p>%1</p><div class="pop-toolbar"><a href="javascript:;" class="circle-btn circle-btn-blue" rel="enter"><span>确定</span></a><a href="#" class="circle-btn circle-btn-white" rel="close"><span>取消</span></a></div></div></div>',
        msgTemp:'<div class="popup-hint"><div class="popup-content"><i class="i-hint ico-%2"><b></b></i><span class="popup-hint-text">%1</span><b class="popup-bg"></b><b class="cor-s sl"><i></i></b><b class="cor-s sr"><i></i></b></div></div>',
        iframeTemp:'<div class="iframe-box" style="width:%2px;height:%3px;"><div class="pop-iframe" style="width:%2px;height:%3px;"><iframe src="%1" frameborder="0" width="%2" height="%3"></iframe></div><div class="pop-bg" style="width:%2px;height:%3px;"></div><a href="javascript:;" rel="close" style="left:%2px;top:-5px;" class="btn-file-close"></a></div>',
        boxType:{
            warm:"i-war",
            suc:"i-suc",
            fail:"i-err",
            hint: "i-war",
            err:"i-err",
            ques:"i-war"
        }
    }
    var _cacheIframeBox,_cacheConfirmBox,_cacheConfirmConBox,_cacheAlertBox,_cacheShowBox,_cacheFormBox,_cacheFormHideBox,_cacheUrlBox;
    var createBox = function(obj){
        obj.type = _Config.boxType[obj.type];
        if(typeof obj.type == "undefined"){
            obj.type = _Config.boxType.hint;
        }
        if(typeof obj.title == "undefined"){
            obj.title = "系统提示";
        }
    }

    var bindBox = function(cachebox,obj){
        $(cachebox).find("[rel]").bind("click",function(){
            if(obj.callback){
                obj.callback();
            }
            Util.ScreenManager.Hide();
            return false;
        });
        if(obj.isClickHide){
            Util.ScreenManager.Show(cachebox,true);
        }
        else{
            Util.ScreenManager.Show(cachebox);
        }
    }

    var Return = {
        /*
        选择提示框
        参数：
        Util.MsgBox.Confirm({
        text: "提示内容",	//[必填]提示内容
        type: "suc",	//[可选]提示类型 warm[警告],suc[成功],fail[失败],hint[信息],err[错误],ques[疑问]  默认为hint
        title:"提示头",	//[可选]提示头
        callback:function(r){} //[可选]点击按钮后执行的方法 r参数：true为点击确定 false为取消
        });
        */
        Confirm: function(obj){
            createBox(obj);
            if(!_cacheConfirmBox){
                _cacheConfirmBox = document.createElement("div");
            }
            _cacheConfirmBox.innerHTML = String.format(_Config.confirmTemp,obj.text,obj.title,obj.type);
            $(_cacheConfirmBox).find("[rel]").bind("click",function(){
                var returnr;
                if(obj.callback){
                    returnr = obj.callback(($(this).attr("rel") == "enter"));
                }
                if(returnr !== false){
                    Util.ScreenManager.Hide();
                }
                return false;
            });
            Util.ScreenManager.Show(_cacheConfirmBox,true);
        },
        /*Iframe提示框
        参数：
        Util.MsgBox.IframeBox({
        url: "http://",
        width: "400",
        height:"500"
        });
        */
        IframeBox: function(obj){
            if(top && top.window.Ext){
                top.window.Ext.App.Message(obj.url);
                return;
            }
            createBox(obj);
            if(!_cacheIframeBox){
                _cacheIframeBox = document.createElement("div");
            }
            _cacheIframeBox.innerHTML = String.format(_Config.iframeTemp,obj.url,obj.width,obj.height);
            $(_cacheIframeBox).find("[rel]").bind("click",function(){
                if(obj.callback){
                    obj.callback(($(this).attr("rel") == "enter"));
                }
                Util.ScreenManager.Hide();
                return false;
            });
            Util.ScreenManager.Show(_cacheIframeBox,true);
        },
        /*
        内容显示
        参数：
        Util.MsgBox.Show({
        text: "提示内容",	//[必填]提示内容
        mType:"提示类型"	//[可选]show[提示],suc[成功],err[失败]
        });
        */
        Show: function(obj){
            createBox(obj);
            if(!_cacheShowBox){
                _cacheShowBox = document.createElement("div");
            }
            var ele = $(String.format(_Config.msgTemp,obj.text,obj.mType));
            if(obj.className){
                ele.addClass(obj.className);
            }            
            if(obj.width){
                ele[0].style.width = obj.width + "px";
            }
            $(_cacheShowBox).html("").append(ele);
            bindBox(_cacheShowBox,obj);
            var left = $(ele).width()/2,docWidth=document.body.clientWidth;
          //  ele.css({
             // left:-left
           // });
            //$(window).resize(function(){
            //	ele.css({
               //	left:(docWidth-$(ele).width())/2
                //});
            //})
            var st = setTimeout(function(){
               Util.ScreenManager.Hide();
            },Util.Config.Result_HideTime);
        }
    }
    return Return;
})();

/**
* 验证
*/
Util.Validate = {
    _reg:{
        intege:"^-?[1-9]\\d*$",					//整数
        num:"^([+-]?)\\d*\\.?\\d+$",			//数字
        url:"^http[s]?:\\/\\/([\\w-]+\\.)+[\\w-]+([\\w-./?%&=]*)?$",	//url
        chinese:"^[\\u4E00-\\u9FA5\\uF900-\\uFA2D]+$",					//仅中文
        notempty:"^.+$",						//非空
        notnull:/[^ \n\r\t　]+/,
        date:"^\\d{4}(\\-|\\/|\.)\\d{1,2}\\1\\d{1,2}$",					//日期
        name:"^[\\u4E00-\\u9FA5\\uF900-\\uFA2Da-zA-Z]([\\s.]?[\\u4E00-\\u9FA5\\uF900-\\uFA2Da-zA-Z]+){1,}$", //真实姓名由汉字、英文字母、空格和点组成，不能以空格开头至少两个字
        username:"^[0-9a-zA-Z_\u0391-\uFFE5]{2,15}$",					//用来用户注册。匹配由数字、26个英文字母中文或者下划线组成的字符串 3-15个字符串之间
        //topictitle:"^([\\u4E00-\\u9FA5\\uF900-\\uFA2D，；。·【】,;\.:\(\)a-zA-Z_0-9\\s]+){4,50}$" //发表新话题
        topictitle:"^.{4,50}$", //发表新话题
        topicprice:"^\\d{0,4}$" //价格
    },
    Check: function(type,value){
        if(Util.Validate._reg[type] == undefined){
            alert("Type " + type + " is not in the data");
            return false;
        }
        var reg;
        if(typeof Util.Validate._reg[type] == "string"){
            reg = new RegExp(Util.Validate._reg[type]);
        }
        else{
            reg = Util.Validate._reg[type];
        }
        return reg.test(value);
    },
    mb_strlen: function(str){
        var offset = 0;
        for(var i=0; i<str.length; i++){
            var string = str.substr(i,1);
            if(escape(string).substr(0,2)=="%u"){
                offset +=3;
            }
            else{
                offset +=1;
            }
        }
        return offset;
    },
    BindForm: function(ele,handler){
        var form = $(ele);
        form.submit(function(){
            if($(this).attr("notvali") == "1"){
                return true;
            }
            var state = true;
            var errorArr = {};
            var checkArr = form.find("[vali]");
            var allArr = {};
            for(var i = 0,len = checkArr.length; i < len; i++){
                var typeArr = $(checkArr[i]).attr("vali").split("|");
                for(var j = 0,jlen = typeArr.length; j < jlen; j++){
                    allArr[i.toString()] = checkArr[i];
                    if($(checkArr[i]).attr("type") == "checkbox"){
                        if(!$(checkArr[i]).attr("checked")){
                            state = false;
                            if(!errorArr[i.toString()]){
                                errorArr[i.toString()] = checkArr[i];
                            }
                            break;
                        }
                    }
                    else if(!(Util.Validate.Check(typeArr[j],checkArr[i].value))){
                        state = false;
                        if(!errorArr[i.toString()]){
                            errorArr[i.toString()] = checkArr[i];
                        }
                        break;
                    }
                    if(typeArr[j] == "notempty"){
                        var min = $(checkArr[i]).attr("min") ? Number($(checkArr[i]).attr("min")) : 0;
                        var max = $(checkArr[i]).attr("max") ? Number($(checkArr[i]).attr("max")) : -1;
                        var count = $.trim(checkArr[i].value).length;
                        if(!(min <= count && (max == -1 || max >= count))){
                            state = false;
                            if(!errorArr[i.toString()]){
                                errorArr[i.toString()] = checkArr[i];
                            }
                        }
                    }
                }
            }            
            if(handler && handler.DefaultCallBack){
                handler.DefaultCallBack(allArr);
            }
            if(handler && handler.ErrorCallBack){
                handler.ErrorCallBack(errorArr);
            }
            if(handler && handler.ReturnCallBack){
                return handler.ReturnCallBack($(this),state);
            }
            else{
                return state;
            }
        });
    },
    CheckDate: function (year, month, day ) {
        var myDate = new Date();
        myDate.setFullYear( year, (month - 1), day );
        return ((myDate.getMonth()+1) == month && day<32);
    }
};
/* 绑定默认字符 */
Util.TextBox = {
    BindDefaultText: function(ele,text){
        if(ele.val() == ""){
            ele.css("color",Util.Config.TextBoxDefaultColor);
            ele.val(text);
        }
        else if(ele.val() == text){
            ele.css("color",Util.Config.TextBoxDefaultColor);
        }
        ele.bind("blur",function(){
            if(ele.val() == ""){
                ele.val(text);
                ele.css("color",Util.Config.TextBoxDefaultColor);
            }
            else{
                ele.css("color",Util.Config.TextBoxActiveColor);
            }
            $(this).css("background","none");
        }).bind("focus",function(){
            ele.css("color",Util.Config.TextBoxActiveColor);
            if(ele.val() == text){
                ele.val("");
            }
            $(this).css("background","#feffeb");
        });
    }
}
//输入框的光标
$(document).bind("mouseover", function(e){
    if(e.target.tagName.toUpperCase() == "INPUT" && e.target.getAttribute("mevent") == "mouse"){
        var input = e.target;
        if(input.type == "text" || input.type == "textarea"){
            if(window.Page_FocusTimer){
                window.clearTimeout(window.Page_FocusTimer);
            }
            window.Page_FocusTimer = window.setTimeout(function(){
                input.focus();
                window.setTimeout(function(){
                    if(input.value){
                        input.select();
                    }
                }, 10);
            }, 200);
        }
    }
}).bind("mouseout", function(e){
    if(e.target.tagName.toUpperCase() == "INPUT" && e.target.getAttribute("mevent") == "mouse"){
        var input = e.target;
        if(input.type == "text" || input.type == "textarea"){
            if(window.Page_FocusTimer){
                window.clearTimeout(window.Page_FocusTimer);
            }
        }
    }
});