//三级联动地区数据
var selArea = new function(){
var self = this, area_data, prov_mod, city_mod;

//初始化区域数据并创建选择框
//可以同时初始化多个
this.init = function(config){
    $.get('/ajax_request/get_area_data', function(rs){
        if (rs.state) {
            area_data = rs.data.list;
            prov_mod  = rs.data.prov_mod;
            city_mod  = rs.data.city_mod;
            if (config.length) {
                for(var i = 0; i < config.length; i++) {
                    self.build_select(config[i]);
                }
            } else {
                self.build_select(config);
            }
        } else {
            alert('无法初始化区域数据!');
        }
    }, 'json');
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
    var sel_id, sel_class, step, name1, name2, name3, sel_value, sel_prov, sel_city, sel_town, p;
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
    var html = "<select name=\""+name1+"\" id=\""+sel_id+"_prov\" class=\""+sel_class+"\" onchange=\"selArea.rebuild_city('"+sel_id+"', this.options[this.selectedIndex].value);\">\n";
    html += sel_prov==0 ? "<option value='0'>请选择</option>\n" :
                          "<option value='0' selected='selected'>请选择</option>\n";
    var provs = this.get_provs( sel_prov );
    for(p in provs) {
        html += provs[p]['sel'] ?  "<option value='"+provs[p]['id']+"' selected='selected'>"+provs[p]['name']+"</option>\n" :
                                   "<option value='"+provs[p]['id']+"'>"+provs[p]['name']+"</option>\n";
    }
    html += "</select>\n";
    
    //城市
    if( step > 1 ) {
        var city_display = "none";
        var city_options = sel_city==0 ? "<option value='0'>选择城市</option>\n" :
                            "<option value='0' selected='selected'>选择城市</option>\n";
        if( sel_prov > 0 ) {
            var citys = selArea.get_citys( sel_prov, sel_city );
            if( citys ) {
                for(p in citys) {
                    city_options += citys[p]['sel'] ?  "<option value='"+citys[p]['id']+"' selected='selected'>"+citys[p]['name']+"</option>\n" :
                                               "<option value='"+citys[p]['id']+"'>"+citys[p]['name']+"</option>\n";
                }
                city_display = "";
            }
        }
        html += "<select name=\""+name2+"\" id=\""+sel_id+"_city\" style=\"display:"+city_display+"\" class=\""+sel_class+"\" onchange=\"selArea.rebuild_town('"+sel_id+"', this.options[this.selectedIndex].value);\">\n";
        html += city_options + "</select>\n";
    }
    
    //县区
    if( step > 2 ) {
        var town_display = "none";
        var town_options = sel_town==0 ? "<option value='0'>选择地区</option>\n" :
                            "<option value='0' selected='selected'>选择地区</option>\n";
        if( sel_city > 0 ) {
            var towns = selArea.get_towns( sel_city, sel_town );
            if( towns ) {
                for(p in towns) {
                    town_options += towns[p]['sel'] ?  "<option value='"+towns[p]['id']+"' selected='selected'>"+towns[p]['name']+"</option>\n" :
                                               "<option value='"+towns[p]['id']+"'>"+towns[p]['name']+"</option>\n";
                }
                town_display = "";
            }
        }
        html += "<select name=\""+name3+"\" style=\"display:"+town_display+"\" class=\""+sel_class+"\" id=\""+sel_id+"_town\">\n";
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
this.rebuild_city = function( cin_id, prov ) {
    var _city = document.getElementById( cin_id+'_city' );
    if( !_city ) return false;
    var aOption = null;
    var citys = selArea.get_citys( prov, 0 );
    _city.options.length = 0;
    aOption = document.createElement('OPTION');
    aOption.text = '选择城市';
    aOption.value = '0';
    _city.options.add(aOption);
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
this.rebuild_town = function( cin_id, city ) {
    var _town = document.getElementById( cin_id+'_town' );
    if( !_town ) return false;
    var aOption = null;
    var towns = selArea.get_towns( city, 0 );
    _town.options.length = 0;
    aOption = document.createElement('OPTION');
    aOption.text = '选择地区';
    aOption.value = '0';
    _town.options.add(aOption);
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