/**
 * @author 蓝面小生
 * @date 2011/10/22
 */
QZ.selectCityCallback = function(prov){
    QZ.SearchCondition.area_id1 = prov;
    QZ.SearchCondition.area_id2 = null;
    QZ.SearchCondition.area_id3 = null;
    var page = QZ.SearchCondition.page.length > 1 ? QZ.SearchCondition.page : 1;
    QZ.getRemoteData(page);
}
QZ.selectTownCallback = function(city){
    QZ.SearchCondition.area_id2 = city;
    QZ.SearchCondition.area_id3 = null;
    var page = QZ.SearchCondition.page.length > 1 ? QZ.SearchCondition.page : 1;
    QZ.getRemoteData(page);
}

QZ.callbackTown = function(val){
    QZ.SearchCondition.area_id3 = this.options[this.selectedIndex].value;
    var page = QZ.SearchCondition.page.length > 1 ? QZ.SearchCondition.page : 1;
    QZ.getRemoteData(page);
}
QZ.dyConfig = {
    step: 3,
    sel_id: 'js_area_sel',
    sel_class: '',
    sel_value: '0',
    name1: 'area_id1',
    name2: 'area_id2',
    name3: 'area_id3',
    callbackCity: "QZ.selectTownCallback",
    callbackProv: "QZ.selectCityCallback",
    callbackTown: "QZ.callbackTown.call(this)"
}
Util.selArea.init(QZ.dyConfig, DM.areas);
$(function(){
    var SearchCondition = {
        cid: "",
        area_id1: "",
        area_id2: "",
        area_id3: "",
        page: "",
        order: 4
    }, OperationArea = {
        wrap: $("div.second-cate"),
        getChildren: function(id){
            return DM.categories[id] && DM.categories[id].children;
        },
        renderHtml: function(data){
            var that = this;
            wrapCon = that.wrap.find(".second-cate-con");
            wrapCon.empty();
            $.each(data, function(i, item){
                var a = $("<a/>").addClass("area-item");
                a.attr("rel", item.cid);
                a.text(item.name);
                wrapCon.append(a);
            });
        },
        getId: function(el){
            return el.attr("rel");
        },
        init: function(el){
            var id = this.getId(el), data = this.getChildren(id);
            if (data) {
                this.renderHtml(data);
                this.wrap.show();
            }
            else {
                this.wrap.hide();
            }
            
            el.parent().after(this.wrap);
            //定位
            var l = el.offset().left, w = el.width(), wl = this.wrap.offset().left;
            this.wrap.find(".second-cate-flag").css("left", l + w / 2 - wl);
            //激活
            $(".n-categroy-info .area-item").removeClass("area-active");
            el.addClass("area-active");
            SearchCondition.cid = id;
        }
    }, getRemoteData = function(page){
        SearchCondition.page = page;
        var url = "/ajax_request/index_category", loadTip = Y.loadTip("正在加载数据...")
        $.get(url, SearchCondition, function(data){
            loadTip.hide();
            if (data.state) {
                renderPage(data.data.total, page, data.data.size);
                //渲染数据
                var tempData = $("#searchResult").tmpl(data.data);
                $(".a-search-con table").empty().append(tempData);
                //悬浮状态
                $(".a-search-con tr").hover(function(){
                    $(this).addClass("hover");
                    $(this).find(".n-add-act,.n-view-act").removeClass("y-hidden");
                }, function(){
                    $(this).removeClass("hover");
                    $(this).find(".n-add-act,.n-view-act").addClass("y-hidden");
                });
                if (!data.data.total) {
                    $("#resultEmpty").removeClass("y-hidden");
                }
                else {
                    $("#resultEmpty").addClass("y-hidden");
                }
                
                if (data.data.total < 7 && data.data.total) {
                    $("#resultLess").removeClass("y-hidden");
                }
                else {
                    $("#resultLess").addClass("y-hidden");
                }
            }
            
        }, "json");
    }, renderPage = function(total, page, pageSize){
        $(".page-info").data("widget_page", false);
        if (total) {
            $(".page-info").page({
                current: page,
                count: total,
                size: pageSize,
                maxPage: 7,
                callBack: function(a, b){
                    getRemoteData(a);
                }
            })
            if (total < pageSize) {
                $(".page-info").empty();
            }
        }
        else {
            $(".page-info").empty();
        }
    }
    QZ.getRemoteData = getRemoteData;
    QZ.SearchCondition = SearchCondition;
    $(".n-categroy-info .area-item").click(function(){
        //获取二级分类
        var that = $(this);
        if (that.hasClass("area-active")) {
            return;
        }
        OperationArea.init(that);
        //发出请求
        getRemoteData(1);
        return false;
    });
    $(".second-cate .area-item").live("click", function(){
        if ($(this).hasClass("area-active")) {
            return;
        }
        $(this).addClass("area-active").siblings().removeClass("area-active");
        var id = OperationArea.getId($(this));
        SearchCondition.cid = id;
        //发出请求
        getRemoteData(1);
        return false;
    });
    //order by
    $(".n-order-by").click(function(){
        var id = $(this).attr("rel");
        if ($(this).hasClass("n-order-active")) {
            return false;
        }
        $(this).addClass("n-order-active").siblings().removeClass("n-order-active");
        SearchCondition.order = id;
        var page = QZ.SearchCondition.page.length > 1 ? QZ.SearchCondition.page : 1;
        getRemoteData(page);
    });
    //去我的城市
    $(".goToMyCity").live("click", function(){
        $(".SelectCity").hide();
        $(".hasSelectCity").text("正在选择城市...").removeClass("y-hidden gray");
        $.get("/ajax_request/my_area_id", function(r){
            $(".hasSelectCity").html('已选择我的城市，<a class="goToMyCity" href="javascript:;">重新定位</a>').addClass("gray");
            $.extend(QZ.dyConfig, {
                sel_value: r
            });
            Util.selArea.init(QZ.dyConfig, DM.areas);
            QZ.SearchCondition.area_id1 = $("#js_area_sel_prov").val();
            QZ.SearchCondition.area_id2 = $("#js_area_sel_city").val();
            QZ.SearchCondition.area_id3 = $("#js_area_sel_town").val();
            getRemoteData(1);
        });
    });
    
    //悬浮显示图片
    $(".n-con-top").hover(function(){
        $(this).find(".n-desc").animate({
            bottom: "+=30"
        }, 200)
    }, function(){
        $(this).find(".n-desc").animate({
            bottom: "-=30"
        }, 200)
    });
    
    /* $(".n-rank-list li").hover(function(){
     $(this).find(".n-desc").animate({
     bottom: "+=60"
     }, 200)
     }, function(){
     $(this).find(".n-desc").animate({
     bottom: "-=60"
     }, 200)
     }) */
    //悬浮状态
    $(".a-search-con tr").hover(function(){
        $(this).addClass("hover");
        $(this).find(".n-add-act,.n-view-act").removeClass("y-hidden");
    }, function(){
        $(this).removeClass("hover");
        $(this).find(".n-add-act,.n-view-act").addClass("y-hidden");
    });
    var total = $(".page-info").attr("data-total"), pageSize = $(".page-info").attr("data-size");
    $(".page-info").page({
        current: 1,
        count: total,
        size: pageSize,
        maxPage: 7,
        callBack: function(a, b){
            getRemoteData(a);
        }
    })
    
    //选项卡切换
    var showResource = $('.n-topic-resource').slider({
        time: 20000
    });
    var showTopic = $('.n-topic-list').slider({
        time: 60000,
        wapper: '.n-r-con',
        scroll: '.n-list-con'
    });
    
    $(".n-topic-top li").each(function(i){
        $(this).click(function(){
            $(this).addClass("active").siblings().removeClass("active");
            $(".n-rank-list").eq(i).show().siblings().hide();
        });
    });
    
    //获取参数
    getParm = function(){
        if (AID!==false || CID!==false) {
            scrollTo(0, 760);
        }
        if (CID) {
            var parentCid = CID[0], subCid = CID[1]
            $(".area-item[rel=" + parentCid + "]").click();
            if (subCid) {
                setTimeout(function(){
                    $(".area-item[rel=" + subCid + "]").click();
                }, 100)
            }
            
        }
        if (AID!==false) {
            var c = $.extend({}, QZ.dyConfig, {
                sel_value: AID
            })
            Util.selArea.init(c, DM.areas);
            QZ.SearchCondition.area_id1 = $("#js_area_sel_prov").val();
            QZ.SearchCondition.area_id2 = $("#js_area_sel_city").val();
            QZ.SearchCondition.area_id3 = $("#js_area_sel_town").val();
            getRemoteData(1);
        }
    }
    getParm();
    //显示头像
    $(".n-user-img li a").hover(function(){
        $(this).parent().addClass("active").siblings().removeClass("active");
        $(this).parent().find(".user-img-detail").css("width", 0).animate({
            width: 122
        }, 200).removeClass("y-hidden");
        //$(".n-user-img-overlay").show();
    }, function(){
        $(this).parent().removeClass("active");
        $(this).parent().find(".user-img-detail").addClass("y-hidden");
        //$(".n-user-img-overlay").hide();
    });
});
