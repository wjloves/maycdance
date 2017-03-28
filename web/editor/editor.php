<!DOCTYPE HTML>
<?php
/*
    [UCenter Home] (C) 2007-2008 Comsenz Inc.
    $Id: editor.php 1 2013-07-18 09:24:31Z haibo $
*/

if(empty($_GET['charset']) || !in_array(strtolower($_GET['charset']), array('gbk', 'big5', 'utf-8'))) $_GET['charset'] = '';
$allowhtml = empty($_GET['allowhtml'])?0:1;

$allowimg = empty($_GET['allowimg'])?0:1;

$allowphoto = empty($_GET['allowphoto'])?0:1;

$doodle = empty($_GET['doodle'])?0:1;

$allowfile = empty($_GET['allowfile']) ? 0 : 1;

$allowpage = empty($_GET['allowpage']) ? 0 : 1;

$bind_textarea = empty($_GET['bind'])? "" : $_GET['bind'];

$domain = empty($_GET['domain'])? "115.com" : $_GET['domain'];

if(empty($_GET['op'])) {
//工具条
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=<?php echo $_GET['charset'];?>" />
<title>Editor</title>
<script type="text/javascript">
document.domain = "<?php echo $domain;?>";
var DOMAIN_URL = "http://<?php echo $_GET['app'];?>.<?php echo $domain;?>/";
</script>
<script language="javascript" src="image/editor/bbcode.js?111104"></script>
<script language="javascript" src="image/editor/editor_base.js?11111"></script>
<link rel="stylesheet" type="text/css" href="../css/reset.css"/>
<style type="text/css">
html {
    _padding-top:32px;
    overflow:hidden;
}
body {
    margin:0;
    padding:0;
    height:100%;
    background:#FFF;
    overflow:hidden;
}
body, td, input, button, select {
    font: 12px/1.5em Tahoma, Arial, Helvetica, snas-serif;
}
.submit {
    padding: 0 10px;
    height: 22px;
    border: 1px solid;
    border-color: #DDD #264F6E #264F6E #DDD;
    background: #2782D6;
    color: #FFF;
    line-height: 20px;
    letter-spacing: 1px;
    cursor: pointer;
}
a.dm {
    text-decoration:none
}
.v-tip{
    margin-top:10px;
}
a.dm:hover {
    text-decoration:underline
}
a {
    font-size:12px
}
img {
    border:0
}
td.icon {
    width:24px;
    height:24px;
    text-align:center;
    vertical-align:middle
}
td.sp {
    width:8px;
    height:24px;
    text-align:center;
    vertical-align:middle
}
td.xz {
    width:47px;
    height:24px;
    text-align:center;
    vertical-align:middle
}
td.bq {
    width:49px;
    height:24px;
    text-align:center;
    vertical-align:middle
}
div a.n {
    height:16px;
    line-height:16px;
    display:block;
    padding:2px;
    color:#000000;
    text-decoration:none
}
div a.n:hover {
    background:#E5E5E5
}
.r_op {
    float: right;
}
.eMenu {
    position:absolute;
    z-index:11;
    margin-top: -2px;
    background:#FFFFFF;
    border:1px solid #C5C5C5;
    padding:4px
}
.eMenu ul, .eMenu ul li {
    margin: 0;
    padding: 0;
}
.eMenu ul li {
    list-style: none;
    float:left
}
#editFaceBox {
    padding: 0px;
}
#editFaceBox li {
    width: 25px;
    height: 25px;
    overflow: hidden;
}
.t_input {
    padding: 3px 2px;
    border-style: solid;
    border-width: 1px;
    border-color: #7C7C7C #C3C3C3 #DDD;
    line-height: 16px;
}
a.n1 {
    height:16px;
    line-height:16px;
    display:block;
    padding:2px;
    color:#000000;
    text-decoration:none
}
a.n1:hover {
    background:#E5E5E5
}
a.cs {
    height:15px;
    position:relative
}
*:lang(zh) a.cs {
height:12px
}
.cs .cb {
    font-size:0;
    display:block;
    width:10px;
    height:8px;
    position:absolute;
    left:4px;
    top:3px;
    cursor:hand!important;
    cursor:pointer
}
.cs span {
    position:absolute;
    left:19px;
    top:0px;
    cursor:hand!important;
    cursor:pointer;
    color:#333
}
.fRd1 .cb {
    background-color:#800
}
.fRd2 .cb {
    background-color:#800080
}
.fRd3 .cb {
    background-color:#F00
}
.fRd4 .cb {
    background-color:#F0F
}
.fBu1 .cb {
    background-color:#000080
}
.fBu2 .cb {
    background-color:#00F
}
.fBu3 .cb {
    background-color:#0FF
}
.fGn1 .cb {
    background-color:#008080
}
.fGn2 .cb {
    background-color:#008000
}
.fGn3 .cb {
    background-color:#808000
}
.fGn4 .cb {
    background-color:#0F0
}
.fYl1 .cb {
    background-color:#FC0
}
.fBk1 .cb {
    background-color:#000
}
.fBk2 .cb {
    background-color:#808080
}
.fBk3 .cb {
    background-color:#C0C0C0
}
.fWt0 .cb {
    background-color:#FFF;
    border:1px solid #CCC
}
.mf_nowchose {
    height:30px;
    background-color:#DFDFDF;
    border:1px solid #B5B5B5;
    border-left:none
}
.mf_other {
    height:30px;
    border-left:1px solid #B5B5B5
}
.mf_otherdiv {
    height:30px;
    width:30px;
    border:1px solid #FFF;
    border-right-color:#D6D6D6;
    border-bottom-color:#D6D6D6;
    background-color:#F8F8F8
}
.mf_otherdiv2 {
    height:30px;
    width:30px;
    border:1px solid #B5B5B5;
    border-left:none;
    border-top:none
}
.mf_link {
    font-size:12px;
    color:#000000;
    text-decoration:none
}
.mf_link:hover {
    font-size:12px;
    color:#000000;
    text-decoration:underline
}
.ico {
    height:24px;
    width:24px;
    vertical-align:middle;
    text-align:center
}
.ico2 {
    height:24px;
    width:27px;
    vertical-align:middle;
    text-align:center
}
.ico3 {
    height:24px;
    width:25px;
    vertical-align:middle;
    text-align:center
}
.ico4 {
    height:24px;
    width:8px;
    vertical-align:middle;
    text-align:center
}
.icons a, .edTb, .sepline, .switch, .tbri {
    background-image:url(image/editor/editor_boolbar.gif)
}
.toobar {
    position:relative;
    height:29px;
    margin:0 5px;
    overflow:hidden
}
.tble {
    position:absolute;
    left:0;
    top:3px
}
*:lang(zh) .tble {
top:2px
}
.tbri {
    width:20px;
    position:absolute;
    right:0;
    top:2px;
    background-position:0 -33px
}
*:lang(zh) .tbri {
top:3px;
background-position:0 -31px
}
.icons a {
    width:23px;
    height:23px;
    background-repeat:no-repeat;
    display:block;
    float:left;
    border:1px solid #f4f4f4;
    border-top:1px solid #f6f6f6;
    border-bottom:1px solid #F2F3F2;
}
*:lang(zh) .icons a {
margin-right:1px
}
.icons a:hover {
    border-top:1px solid #CCC;
    border-right:1px solid #999;
    border-bottom:1px solid #999;
    border-left:1px solid #CCC;
    background-color:#FFF;
}
a.icoCut {
    background-position:1px 2px;
}
a.icoCpy {
    background-position:-27px 1px;
}
a.icoPse {
    background-position:-55px 1px
}
a.icoFfm {
    background-position:-82px 1px;
    width:27px
}
a.icoFsz {
    background-position:-111px 1px;
}
*:lang(zh) a.icoFsz {
margin:0
}
a.icoWgt {
    background-position:-139px 0;
}
*:lang(zh) a.icoWgt {
width:21px
}
a.icoIta {
    background-position:-166px 0;
}
*:lang(zh) a.icoIta {
width:21px
}
a.icoUln {
    background-position:-196px 1px;
}
*:lang(zh) a.icoUln {
margin:0
}
a.iconPreview{
    background:url(image/search_page.gif) 0 1px no-repeat;
}
a.icoPage{
    background:url(image/search_page.gif) -28px 1px no-repeat;
}
a.icoAgn {
    background-position:-224px 1px
}
a.icoLst {
    background-position:-252px 1px
}
a.icoOdt {
    background-position:-309px 1px
}
a.icoIdt {
    background-position:-308px 1px
}
a.icoFcl {
    background-position:-335px 1px
}
a.icoBcl {
    background-position:-362px 1px
}
a.icoUrl {
    background-position:-392px 1px;
}
a.icoMoveUrl {
    background-position:-486px 1px
}
a.icoRenew {
    background-position:-519px 1px
}
a.icoFace {
    background-position:-553px 1px
}
a.icoDoodle {
    background-position:-584px 1px
}
a.icoImg {
    background-position:-420px 1px
}
a.icoUImg {
    background-position:-613px 1px
}
a.icoSwf {
    background-position:-447px 1px
}
a.icoSwitchTxt {
    background-position:-638px 0px;
    width:23px;
    float:right
}
a.icoSwitchMdi {
    background-position:-671px 0px;
    width:23px
}
.edTb {
    position:absolute;
    top:0;
    right:0;
    left:0;
    height:31px;
    border-bottom:1px solid #ddd;
    background:url(image/tb_bar.gif) repeat-x;
}
.sepline {
    width:4px;
    height:20px;
    margin-top:2px;
    margin-right:3px;
    background-position:-476px 0;
    background-repeat:no-repeat;
    float:left
}
.main-contents {
    position:absolute;
    top:32px;
    right:0;
    bottom:0;
    left:0;
    z-index:1;
    _position:relative;
    _top:0;
    _widht:100%;
    _height:100%;
}
.main-contents iframe {
    position:absolute;
    top:0;
    right:0;
    bottom:0;
    left:0;
    width:100%;
    height:100%;
}
.main-contents textarea {
    width:100%;
    height:100%;
    margin:0;
    padding:0 0 0 5px;
    font:14px/1.8 Tahoma, Geneva, sans-serif;
    border:0 none;
    background:#FFF;
    outline:none;
    overflow:auto;
 *height:278px;
}
.faceTab{
height:25px;
border-bottom:1px solid #ddd;
background:#f8f8f8;
}
.face-tab,.face-tab:hover{
 display:inline-block;
 padding:0 10px;
 line-height:25px;
 color:#999;
 height:25px;
}
.face-tab-active{
    background:#fff;
    position:relative;
    top:1px;
    border-left:1px solid #ddd;
    border-right:1px solid #ddd;
    color:#444
}
#normal,#yellowFace{
    padding:5px;
}
#normal li{
    height: 30px;
    overflow: hidden;
    width: 30px;
    text-align:center;
    margin-left:2px;
    display:inline;
}
#yellowFace li{
    text-align:center;
    margin-left:2px;
    display:inline;
}
#icoMusic{
    background:url("image/editor/xiami.gif") 3px 3px no-repeat;
}
a.icoCode{
 background:url("image/page_white_code.png") 3px 3px no-repeat;
}
-->
</style>
<script type="text/javascript">
<!--
function fontname(obj){format('fontname',obj.innerHTML);obj.parentNode.style.display='none'}
function fontsize(size,obj){format('fontsize',size);obj.parentNode.style.display='none'}
//-->
</script>
</head>
<body>
<div id="dvHtmlLnk" style="display:none">
    <div class="edTb">
        <div class="toobar">
            <div class="icons tble"> <a href="javascript:;" class="icoSwitchMdi" title="切换到多媒体" onClick="changeEditType(true);return false;"></a> </div>
        </div>
    </div>
    <div class="main-contents">
        <textarea id="dvtext"></textarea>
    </div>
</div>
<div id="dvhtml" style="*zoom:1;_zoom:0;">
    <div class="edTb">
        <div class="toobar">
            <div class="icons tble"> 
                <a href="javascript:;" class="icoCut" title="剪切" onClick="format('Cut');return false;"></a> 
                <a href="javascript:;" class="icoCpy" title="复制" onClick="format('Copy');return false;"></a> 
                <a href="javascript:;" class="icoPse" title="粘贴" onClick="format('Paste');return false;"></a>
                <?php if($allowpage) { ?>
                <a href="javascript:;" class="iconPreview" id="textPreview" title="预览发言" onClick="previewPage();return false;"></a>
                <?php }?>
                <div class="sepline"></div>
                <a href="javascript:;" class="icoFfm" id="imgFontface" title="字体" onClick="fGetEv(event);fDisplayElement('fontface','');return false;"></a> 
                <a href="javascript:;" class="icoFsz" id="imgFontsize" title="字号" onClick="fGetEv(event);fDisplayElement('fontsize','');return false;"></a> 
                <a href="javascript:;" class="icoWgt" onClick="format('Bold');return false;" title="加粗"></a> 
                <a href="javascript:;" class="icoIta" title="斜体" onClick="format('Italic');return false;"></a> 
                <a href="javascript:;" class="icoUln" onClick="format('Underline');return false;" title="下划线"></a> 
                <a href="javascript:;" class="icoFcl" title="字体颜色" onClick="foreColor(event);return false;" id="imgFontColor"></a> 
                <a href="javascript:;" class="icoAgn" id="imgAlign" onClick="fGetEv(event);fDisplayElement('divAlign','');return false;" title="对齐"></a> 
                <a href="javascript:;" class="icoLst" id="imgList" onClick="fGetEv(event);fDisplayElement('divList','');return false;"title="编号"></a> 
                <a href="javascript:;" class="icoOdt" id="imgInOut" onClick="fGetEv(event);fDisplayElement('divInOut','');return false;" title="缩进"></a>
                
                <?php if($allowpage) { ?>
                <a href="javascript:;" class="icoPage" id="imgPage" onClick="fGetEv(event);fDisplayElement('divPage','');return false;" title="文章分页"></a>
                <?php }?>
                
                <div class="sepline"></div>
                
                <a href="javascript:;" class="icoUrl" id="icoUrl" onClick="createLink(event, 1);return false;" title="超链接"></a> 
                <a href="javascript:;" class="icoMoveUrl" onClick="clearLink();return false;" title="移除链接"></a>
                <a href="javascript:;" class="icoCode" id="createCode" onClick="createCode();return false;" title="插入代码"></a>
                <?php if($allowimg){?>
                <a href="javascript:;" class="icoImg" id="icoImg" onClick="createImg(event, 1);return false;" title="插入网络图片"></a>
                <?php if($allowphoto){?><a href="javascript:;" class="icoUImg" id="icoUImg" onClick="showPhotoBox();return false;" title="插入115网盘相册图片"></a><?php }?>
                <a href="javascript:;" class="icoSwf" id="icoSwf" onClick="createFlash(event, 1);return false;" title="引用视频FLASH"></a>
                <a href="javascript:;" class="icoMusic" id="icoMusic" onClick="createMusic();return false;" title="插入音乐"></a>
                <a href="javascript:;" class="icoFace" id="faceBox" onClick="faceBox(event);return false;" title="插入表情"></a>
                <?php }?>
                
                <div class="sepline"></div>
                <?php if($allowfile) { ?>
                <a href="javascript:;" class="icoDoodle" id="fileBox" onClick="showFileBox();return false;" title="插入115网盘文件"></a>
                <?php }?>
                <?php if($doodle) { ?>
                <a href="javascript:;" class="icoDoodle" id="doodleBox" onClick="doodleBox(event, this.id);return false;" title="涂鸦"></a>
                <?php }?>
                <?php if($allowhtml) {?>
                <input type="checkbox" value="1" name="switchMode" id="switchMode" style="float:left;margin-top:6px!important;margin-top:2px" onClick="setMode(this.checked)" onMouseOver="fSetModeTip(this)" onMouseOut="fHideTip()" />
                <?php } else {?>
                <input type="hidden" value="1" name="switchMode" id="switchMode">
                <?php }?>
                                
                <!--<a href="javascript:;" class="icoRenew" onClick="renewContent();return false;" title="恢复内容"></a>-->
            </div>
            <!--<div class="icons tbri"> <a href="javascript:;" class="icoSwitchTxt" title="切换到纯文本" onClick="changeEditType(false, event);return false;"></a> </div>-->
        </div>

        <!--纯文本状态工具栏-->
        <!--
        <div class="toobar" style="display:none" id="dvHtmlLnk">
            <div class="icons tble"> <a href="javascript:;" class="icoSwitchMdi" title="切换到多媒体" onClick="changeEditType(true, event);return false;"></a> </div>
        </div>
        -->
    </div>
    <div style="width:100px;height:100px;position:absolute;display:none;top:-500px;left:-500px" ID="dvPortrait"></div>
    <div id="fontface" class="eMenu" style="z-index:99;display:none;top:35px;left:2px;width:110px;height:180px">
        <a href="javascript:void(0)" onClick="fontname(this)" class="n" style="font:normal 12px '宋体';">宋体</a>
        <a href="javascript:void(0)" onClick="fontname(this)" class="n" style="font:normal 12px '黑体';">黑体</a>
        <a href="javascript:void(0)" onClick="fontname(this)" class="n" style="font:normal 12px '楷体_GB2312';">楷体_GB2312</a>
        <a href="javascript:void(0)" onClick="fontname(this)" class="n" style="font:normal 12px '幼圆';">幼圆</a>
        <a href="javascript:void(0)" onClick="fontname(this)" class="n" style="font:normal 12px Arial;">Arial</a>
        <a href="javascript:void(0)" onClick="fontname(this)" class="n" style="font:normal 12px 'Arial Narrow';">Arial Narrow</a> 
        <a href="javascript:void(0)" onClick="fontname(this)" class="n" style="font:normal 12px 'Arial Black';">Arial Black</a> 
        <a href="javascript:void(0)" onClick="fontname(this)" class="n" style="font:normal 12px 'Comic Sans MS';">Comic Sans MS</a> 
        <a href="javascript:void(0)" onClick="fontname(this)" class="n" style="font:normal 12px System;">System</a> 
    </div>
    <div id="fontsize" class="eMenu" style="display:none;top:35px;left:26px;width:125px;height:120px"> 
        <a href="javascript:void(0)" onClick="fontsize(1,this)" class="n" style="font-size:xx-small;line-height:120%;">极小</a> 
        <a href="javascript:void(0)" onClick="fontsize(2,this)" class="n" style="font-size:x-small;line-height:120%;">特小</a> 
        <a href="javascript:void(0)" onClick="fontsize(3,this)" class="n" style="font-size:small;line-height:120%;">小</a> 
        <a href="javascript:void(0)" onClick="fontsize(4,this)" class="n" style="font-size:medium;line-height:120%;">中</a> 
        <a href="javascript:void(0)" onClick="fontsize(5,this)" class="n" style="font-size:large;line-height:120%;">大</a> 
    </div>
    <div id="divList" class="eMenu" style="display:none;top:35px;left:26px;width:60px;height:40px;">
        <a href="javascript:void(0)" onClick="format('Insertorderedlist');fHide(this.parentNode)" class="n">数字列表</a>
        <a href="javascript:void(0)" onClick="format('Insertunorderedlist');fHide(this.parentNode)" class="n">符号列表</a>
    </div>
    <div id="divAlign" class="eMenu" style="display:none;top:35px;left:26px;width:60px;height:60px;">
        <a href="javascript:void(0)" onClick="format('Justifyleft');fHide(this.parentNode)" class="n">左对齐</a>
        <a href="javascript:void(0)" onClick="format('Justifycenter');fHide(this.parentNode)" class="n">居中对齐</a>
        <a href="javascript:void(0)" onClick="format('Justifyright');fHide(this.parentNode)" class="n">右对齐</a>
    </div>
    <div id="divInOut" class="eMenu" style="display:none;top:35px;left:26px;width:60px;height:40px;">
        <a href="javascript:void(0)" onClick="format('Indent');fHide(this.parentNode)" class="n">增加缩进</a>
        <a href="javascript:void(0)" onClick="format('Outdent');fHide(this.parentNode)" class="n">减少缩进</a>
    </div>
    <div id="divPage" class="eMenu" style="display:none;top:35px;left:26px;width:60px;height:80px;">
        <a href="javascript:void(0)" onClick="insertPage('artpf');fHide(this.parentNode)" class="n" title="插入分页标记">插入分页</a>
        <a href="javascript:void(0)" onClick="insertPage('artpfauto');fHide(this.parentNode)" class="n" title="自动分页">自动分页</a>
        <a href="javascript:void(0)" onClick="insertPage('artpfcancel');fHide(this.parentNode)" class="n" title="取消分页">取消分页</a>
        <a href="javascript:void(0)" onClick="previewPage()" class="n" title="预览">预览</a>
    </div>
    <div id="dvForeColor" class="eMenu" style="display:none;top:35px;left:26px;width:90px;"> 
        <!--<a href="javascript:void(0)" onClick="format(gSetColorType,'#800000')" class="n cs fRd1"><b class="cb"></b><span>暗红色</span></a>-->
        <a href="javascript:void(0)" onClick="format(gSetColorType,'#800080')" class="n cs fRd2"><b class="cb"></b><span>紫色</span></a>
        <a href="javascript:void(0)" onClick="format(gSetColorType,'#F00000')" class="n cs fRd3"><b class="cb"></b><span>红色</span></a> 
        <!--<a href="javascript:void(0)" onClick="format(gSetColorType,'#F000F0')" class="n cs fRd4"><b class="cb"></b><span>鲜粉色</span></a>-->
        <!--<a href="javascript:void(0)" onClick="format(gSetColorType,'#000080')" class="n cs fBu1"><b class="cb"></b><span>深蓝色</span></a>--> 
        <a href="javascript:void(0)" onClick="format(gSetColorType,'#0000F0')" class="n cs fBu2"><b class="cb"></b><span>蓝色</span></a> 
        <!--<a href="javascript:void(0)" onClick="format(gSetColorType,'#00F0F0')" class="n cs fBu3"><b class="cb"></b><span>湖蓝色</span></a>--> 
        <!--<a href="javascript:void(0)" onClick="format(gSetColorType,'#008080')" class="n cs fGn1"><b class="cb"></b><span>蓝绿色</span></a>-->
        <a href="javascript:void(0)" onClick="format(gSetColorType,'#008000')" class="n cs fGn2"><b class="cb"></b><span>绿色</span></a> 
        <!--<a href="javascript:void(0)" onClick="format(gSetColorType,'#808000')" class="n cs fGn3"><b class="cb"></b><span>橄榄色</span></a>-->
        <!--<a href="javascript:void(0)" onClick="format(gSetColorType,'#00F000')" class="n cs fGn4"><b class="cb"></b><span>浅绿色</span></a>-->
        <!--<a href="javascript:void(0)" onClick="format(gSetColorType,'#F0C000')" class="n cs fYl1"><b class="cb"></b><span>橙黄色</span></a>-->
        <a href="javascript:void(0)" onClick="format(gSetColorType,'#F0C000')" class="n cs fYl1"><b class="cb"></b><span>黄色</span></a> 
        <a href="javascript:void(0)" onClick="format(gSetColorType,'#000000')" class="n cs fBk1"><b class="cb"></b><span>黑色</span></a> 
        <a href="javascript:void(0)" onClick="format(gSetColorType,'#808080')" class="n cs fBk2"><b class="cb"></b><span>灰色</span></a> 
        <a href="javascript:void(0)" onClick="format(gSetColorType,'#C0C0C0')" class="n cs fBk3"><b class="cb"></b><span>银色</span></a> 
        <a href="javascript:void(0)" onClick="format(gSetColorType,'#FFFFFF')" class="n cs fWt0"><b class="cb"></b><span>白色</span></a> 
    </div>
    <div id="editFaceBox" class="eMenu" style="display:none;top:35px;left:26px;width:300px;"></div>
    <div id="createUrl" class="eMenu" style="display:none;top:75px;left:26px;width:300px;font-size:12px"> 请输入选定文字链接地址:<br/>
        <input type="text" id="insertUrl" name="url" value="http://" class="t_input" style="width: 190px;">
        <input type="button" onClick="createLink();" name="createURL" value="确定" class="submit" />
        <a href="javascript:;" onClick="fHide($('createUrl'));">取消</a> </div>
    
    <div id="createImg" class="eMenu" style="display:none;top:35px;left:26px;width:380px;font-size:12px">
        <div style="color:gray">请输入图片URL地址:<br/>
        <input type="text" id="imgUrl" name="imgUrl" value="http://" class="t_input" style="width: 270px;" />
        <input type="button" onClick="createImg();" name="createURL" value="确定" class="submit" />
        <a href="javascript:;" onClick="fHide($('createImg'));">取消</a></div>
    </div>

    <div id="createSwf" class="eMenu" style="display:none;top:35px;left:26px;width:460px;font-size:12px">
        粘帖视频地址:<br/>
        <!--<select name="vtype" id="vtype">
            <option value="0">Flash动画</option>
            <option value="1">Media视频</option>
            <option value="2">Real视频</option>
        </select>-->
        <input type="text" id="videoUrl" name="videoUrl" value=""  class="t_input" style="width: 200px;" />
        宽:
        <select name="vWidth" id="vWidth">
            <option value="">默认</option>
            <option value="60">60</option>
            <option value="120">120</option>
            <option value="240">240</option>
            <option value="360">360</option>
            <option value="480">480</option>
            <option value="550">550</option>
            <option value="600">600</option>
            <option value="800">800</option>
        </select>
        高:
        <select name="vHeight" id="vHeight">
            <option value="">默认</option>
            <option value="30">30</option>
            <option value="60">60</option>
            <option value="120">120</option>
            <option value="200">200</option>
            <option value="320">320</option>
            <option value="400">400</option>
            <option value="500">500</option>
            <option value="600">600</option>
        </select>
        <input type="button" onClick="createFlash();" name="createURL" value="确定" class="submit" />
        <a href="javascript:;" onClick="fHide($('createSwf'));">取消</a>
        <p class="v-tip">目前已支持
            <a href="http://www.youku.com/" target="_blank">优酷网</a>、
            <a href="http://www.tudou.com/" target="_blank">土豆网</a>、
            <a href="http://www.ku6.com/" target="_blank">酷6网</a>、
            <a href="http://video.sina.com.cn" target="_blank">新浪网</a>、
            <a href="http://www.56.com/" target="_blank">56网</a>等网站。
            <a href="/115/24262" target="_blank">如何使用?</a></p>
    </div>
    <script type="text/javascript">
    <!--
        var parentTextAreaId = '<?php echo $bind_textarea;?>';
        var ChildInterface;
        function blank_load() {
            var inihtml = '';
            var obj = parent.document.getElementById(parentTextAreaId);
            if(obj) {
                inihtml = bbcode2html(obj.value);
            }
            //解决不能换行的问题
            if(!inihtml && (BROWSER.firefox || BROWSER.opera)) {
                inihtml = '<br>' + initcontent;
            }

            //var ifrm = document.getElementById('HtmlEditor');
            //ifrm.contentWindow.document.body.innerHTML = inihtml;

            if(ChildInterface){
                ChildInterface.html(inihtml);
            }
        }
        var set_child_content = function(interface){
            ChildInterface = interface;
            blank_load();
        }
    //-->
    </script>
    <div class="main-contents">
        <div id="divEditor">
            <iframe class="HtmlEditor" id="HtmlEditor" name="HtmlEditor" frameborder="0" src="editor.php?op=blank&domain=<?php echo $domain;?>&charset=<?php echo $_GET['charset'];?>"></iframe>
        </div>
        <textarea id="sourceEditor" style="display:none;" wrap="off"></textarea>
    </div>
</div>
<input type="hidden" name="uchome-editstatus" id="uchome-editstatus" value="html">
</body>
</html>
<?php

} else {

//空白页面
?>
<script type="text/javascript">
var focusBody = function(){
    if(document.all){
        document.getElementById('js_edit_body').focus();
    }
}

</script>
<html onclick="focusBody();">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=<?php echo $_GET['charset'];?>" />
<title>New Document</title>
<meta content="mshtml 6.00.2900.3132" name=generator />
<script type="text/javascript">
document.domain = "<?php echo $domain;?>";
</script>
<style>
html{overflow-x:hidden;}
body {
    margin:0;
    padding:0 5px;
    background:#FFF;
    color:#2B2B2B;
    font-size:14px;
    line-height:1.5em;
}
* {
    padding:0;
    margin:0;
}
pre{
    background:#f8f8f8;
    border:1px solid #eee;
    font-family: "Consolas","Courier New",Courier,mono,serif;
    font-size:12px;
    margin:5px;
}
</style>
</head>

<body contentEditable="true" spellcheck="false" id="js_edit_body"></body>
<script type="text/javascript">
    var editkeyup = editkeydown = function(e){
        if(window.event){
            e = window.event;
        }
        if(e.ctrlKey && e.keyCode == 13){
            if(parent){
                if(parent.window.saveCallback){
                    parent.window.saveCallback();
                    return false;
                }
                //parent.window.saveCallback && parent.window.saveCallback();
            }
        }
        if(parent&&parent.window.QZ){
             parent.window.QZ.editKeyup && parent.window.QZ.editKeyup();
        }
    }
    var editpaste = function(){
        var source = document.getElementById('js_edit_body');
        if (parent.window.PasteFromWord(source)){
            var tip;
            if (parent && parent.parent && parent.parent.Y) {
                tip = parent.parent.Y.loadTip("正在清理WORD粘贴内容...");
            }
            setTimeout(function(){
                var fHtml = parent.window.CleanWord(source, false, true);
                source.innerHTML = fHtml;
                tip && tip.hide();
            }, 15);
        }
    }
    // 兼容safari组合键换行问题
    if (parent.BROWSER && parent.BROWSER.safari){
        document.getElementById('js_edit_body').onkeydown = editkeydown;
    } else {
        document.getElementById('js_edit_body').onkeydown = editkeyup;
    }
    // 粘贴时过滤WORD内容
    document.getElementById('js_edit_body').onpaste = function(e){
        setTimeout(editpaste, 0);
    };
    
    if(parent){
        parent.window.set_child_content({
            html: function(html){
                document.body.innerHTML = html;
                var f = window.frames["HtmlEditor"];
                document.onclick = function(){
                    parent.window.fHideMenu();
                }
                if(document.all) {
                    document.attachEvent("onkeydown", parent.window.listenKeyDown);
                    document.attachEvent("onkeyup", parent.window.listenKeyUp);
                } else {
                    document.addEventListener('keydown',function(e) {parent.window.listenKeyDown();}, true);
                    document.addEventListener("keyup",function(e){parent.window.listenKeyUp(e);},true);
                }
            }
        });
    }
</script>
</html>
<?php }?>